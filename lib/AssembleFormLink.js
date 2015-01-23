(function (mw, $) {

    mw.libs.AssembleFormLink = {

        /**
         * Looks for form inputs that requested values from a category
         * Fetches all pages that fit the category
         * pre-populates the values for autocompletion
         */
        fetchValuesFromCategory: function() {
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
            jQuery.each(categoriesToFetch, function(categoryName, val) {

                $.getJSON(apiUrl, {
                    action: "query",
                    list: "categorymembers",
                    cmtitle: "Category:" + categoryName,
                    cmlimit: 999,
                    format: 'json'
                })
                .done(function(json, textStatus, jqXHR) {

                    var affectedElements = $('select[data-values-from-category=' + categoryName + ']');

                    if (json && json.query && json.query.categorymembers) {

                        affectedElements.each(function() {
                            for (var i = 0; i < json.query.categorymembers.length; i++) {
                                var title = json.query.categorymembers[i].title;
                                $(this).append('<option>' + title + '</option>');
                            };
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
        }
    };

    mw.libs.AssembleFormLink.submit = function(event) {

        var form = event.target;
        var url  = '';

        for (var i = 0; i < form.length; i++) {
            var inputEl = form[i];
            if (inputEl.type !== 'submit') {
                url += form[i].value;
            }
        };

        // Go to new location
        window.location.assign(url);
        event.preventDefault();
        return false;
    },

    mw.libs.AssembleFormLink.triggerSelect2 = function(elements) {

        elements.each(function() {
            var el = $(this);

            // BUGFIX-HACK: Remove all data- attributes first, select2 will crash otherwise.
            el.removeAttr('data-select');
            el.removeAttr('data-select2');
            el.removeAttr('data-values-from-category');

            el.select2({
                width: "element"
            });
        })
    }


    $(function() {

        mw.libs.AssembleFormLink.fetchValuesFromCategory();

        /**
         * If a submit button from a AssembledFormLink form is clicked, execute this
         *
         * Will parse all the input elements of the form and generate an URL out of it
         * This URL will open a new form with the correct page title already calculated
         *
         * @param  {[type]} event [description]
         * @return {[type]}       [description]
         */
        $(".cfl-form").bind("submit", mw.libs.AssembleFormLink.submit);

    });

}(mediaWiki, jQuery));