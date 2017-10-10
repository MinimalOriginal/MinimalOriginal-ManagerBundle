CKEDITOR.plugins.add('mediasbrowser', {
    lang: 'fr,en',
    icons: 'mediasbrowser',
    init: function (editor) {
        var config = editor.config,
            mediasBrowserRoute = config.mediasBrowserRoute,
            thumbFormat = config.mediasBrowserThumbFormat;
        var route = mediasBrowserRoute.route;
        var route_params = typeof(mediasBrowserRoute.params) != 'undefined' ? mediasBrowserRoute.params : null;
        /*
          Needs FOS JS Routing
        */
        if( typeof(Routing) == 'undefined' ){
          console.warn('FOS JS Routing plugin expected for ckeditor image browser');
          return;
        }

        editor.addCommand('mediasbrowser', {
            exec: function (editor) {
              /*
                Needs Foundation.Reveal
              */
              if( typeof(Foundation.Reveal) == 'undefined' ){
                console.warn('Foundation Reveal plugin expected for ckeditor image browser');
                return;
              }
              var modal = $('<div>',{class: 'reveal', 'data-reveal': true});
              $('body').appendmodal

              var fndmodal = new Foundation.Reveal(modal);

                window.selectMedia = function(data) {
                    editor.insertHtml(data.html.replace('_prototype', thumbFormat));
                    modal.foundation('close');

                };

                $.ajax({
                    url: Routing.generate(route,route_params),
                    xhrFields: {withCredentials: true},
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (content) {
                        modal.html(content);
                        fndmodal.foundation('open');
                    },
                });
            }
        });

        if (editor.ui.addButton) {
            editor.ui.addButton('Mediasbrowser', {
                label: editor.lang.mediasbrowser['label'],
                command: 'mediasbrowser',
                toolbar: 'insert',
            });
        }
    }
});
