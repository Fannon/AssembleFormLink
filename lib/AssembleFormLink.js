/**
 * AssembleFormLink Extension
 *
 * https://www.mediawiki.org/wiki/Extension:AssembleFormLink
 *
 * @author  Simon Heimler
 */
(function (mw, $) {

    'use strict';

    /** @type {Object} namespace */
    mw.libs.AssembleFormLink = {};


    /**
     * Looks for form inputs that requested values from a category
     * Fetches all pages that fit the category
     * pre-populates the values for autocompletion
     */
    mw.libs.AssembleFormLink.fetchValuesFromCategory = function() {

        var apiUrl = mw.config.get('wgScriptPath') + '/api.php';
        var categoriesToFetch = {};
        var valuesFromCategoryInputs = $('*[data-values-from-category]');

        // Look for all input fields that require remote autocomplete values
        // Don't add them twice, since it would make no sense to fetch the same data more than once
        valuesFromCategoryInputs.each(function() {
            var category = valuesFromCategoryInputs.attr('data-values-from-category');
            categoriesToFetch[category] = true;
        });

        // Iterates each category to fetch.
        // get the page names from the MediaWiki API through an AJAX request
        // Fill all select boxes with those values that requested them
        $.each(categoriesToFetch, function(categoryName, val) {

            $.getJSON(apiUrl, {
                action: 'query',
                list: 'categorymembers',
                cmtitle: 'Category:' + categoryName,
                cmlimit: 999,
                format: 'json'
            })
            .done(function(json) {

                var affectedElements = $('select[data-values-from-category=' + categoryName + ']');

                if (json && json.query && json.query.categorymembers) {

                    affectedElements.each(function() {
                        for (var i = 0; i < json.query.categorymembers.length; i++) {
                            var title = json.query.categorymembers[i].title;

                            // TODO: This should be part of a separate values-from-namespace opton
                            if (title.indexOf(':') > -1) {
                                title = title.split(":").pop();
                            }
                            $(this).append('<option>' + title + '</option>');
                        }
                    });
                    mw.libs.AssembleFormLink.triggerSelect2(affectedElements);

                } else {
                    console.warn('Could not fetch categories from API');
                    console.dir(json);
                }

            })
            .fail(function(jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                console.error(err);
            });

        });
    };

    mw.libs.AssembleFormLink.submit = function(event) {

        var form = event.target;
        var url  = '';

        for (var i = 0; i < form.length; i++) {
            var inputEl = form[i];
            if (inputEl.type !== 'submit') {
                url += form[i].value;
            }
        }

        url += '?AssembleFormLink=true';

        // Look for the input fields that were filled out and attach the values as GET parameter
        var inputs = $(form).find('*[name]');

        inputs.each(function() {
            var key = $(this).attr('name');
            var value = $(this).val();
            url += '&' + key + '=' + encodeURIComponent(value);
        });

        // Go to new location
        location.assign(url);
        // console.info(url);

        // Prevent default submit behaviour
        event.preventDefault();
        return false;
    };

    mw.libs.AssembleFormLink.triggerSelect2 = function(elements) {

        elements.each(function() {
            var el = $(this);

            // BUGFIX-HACK: Remove all data- attributes first, select2 will crash otherwise.
            el.removeAttr('data-select');
            el.removeAttr('data-select2');
            el.removeAttr('data-values-from-category');

            try {
                el.select2({
                    // width: 'element'
                });

                el.select2("val", null)
            } catch(e) {
                console.error('select2 widget crashed');
                console.error(e);
            }

        });
    };

    mw.libs.AssembleFormLink.autoPopulateSemanticForm = function() {

        // Convert URL GET parameters to a JavaScript Object
        // @see http://stackoverflow.com/a/8649003
        var search = location.search.substring(1);
        var getParameters;

        try {
            getParameters = JSON.parse('{"' + decodeURI(search)
                .replace(/"/g, '\\"')
                .replace(/&/g, '","')
                .replace(/=/g,'":"') + '"}');
        } catch (e) {
            getParameters = false;
        }

        // If an AssembleFormLink parameter is given:
        // Get the rest of the parameters and try to fill them into the form.
        // If a field is already filled, skip it.
        if (getParameters && getParameters.AssembleFormLink) {
            delete getParameters.AssembleFormLink;
            delete getParameters.title;

            $.each(getParameters, function(key, value) {

                var input = $('#sfForm .attr_' + key);

                // console.log(key + ':' + value);
                // console.log(input);

                if (!input.val() || input.val() === '') {

                    var finalValue = decodeURIComponent(value);
                    finalValue = finalValue.split('+').join(' ');

                    input.val(finalValue);

                    // if it is a select2 widget, set the value programmatically
                    if (input.hasClass('select2-container')) {
                        // TODO: Won't work :(
                        // Should look like:
                        // $('#s2id_input_2').select2("val", "Apple");
                    }
                }
            });
        }
    };

    // On DOM ready:
    $(function() {

        try {
            var aflForm = $('.afl-form');

            // CASE: An AssembleFormLink form is detected. Make it work.
            if (aflForm.length > 0) {

                mw.libs.AssembleFormLink.fetchValuesFromCategory();

                /**
                 * If a submit button from a AssembledFormLink form is clicked, execute this
                 *
                 * Will parse all the input elements of the form and generate an URL out of it
                 * This URL will open a new form with the correct page title already calculated
                 */
                aflForm.bind('submit', mw.libs.AssembleFormLink.submit);


            } else if ($('#sfForm').length > 0) {
                // CASE: A SemanticForm is detected. Check if there are field-values in the URL GET parameter
                // If they are, automatically insert them into the form (if the field would be empty otherwise)

                // TODO: Broken:
                mw.libs.AssembleFormLink.autoPopulateSemanticForm();
            }

            // If neither an AFL or SemanticForm is found, do nothing!

        } catch(e) {
            console.error('AssembleFormLink crashed!');
            console.error(e);
        }

    });


}(mediaWiki, jQuery));