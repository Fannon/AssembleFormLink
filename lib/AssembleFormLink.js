$(function() {


    /**
     * Is called through a submit button
     * Will parse all the input elements of the form and generate an URL out of it
     * This URL will open a new form with the correct page title already calculated
     *
     * @param  {[type]} event [description]
     * @return {[type]}       [description]
     */
    $(".cfl-form").bind("submit", function(event) {

        var form = event.target;
        var url  = '';

        for (var i = 0; i < form.length; i++) {
            var inputEl = form[i];

            console.log(inputEl);

            if (inputEl.type !== 'submit') {
                url += form[i].value;
            }

        };

        window.location.replace(url);

        event.preventDefault();
     });

});
