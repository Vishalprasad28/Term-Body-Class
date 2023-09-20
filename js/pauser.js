(function ($, Drupal, once) {
    Drupal.behaviors.myModuleBehavior = {
      attach: function (context, settings) {
        once('body', 'html', context).forEach(function (element) {
          let current = {
            audio: null,
            video: null
          }
          $('audio').on('playing', function() {
            itemPlay(this, current, 'audio');
          });
          $('video').on('playing', function() {
            itemPlay(this, current, 'video');
          });
          function stop(item) {
            item.pause();
          }
          function itemPlay(item, current, type) {
            if (type === 'audio') {
              if (current.audio && current.audio !== item) {
                stop(current.audio);
              }
              current.audio = item;
            }
            else {
              if (current.video && current.video !== item) {
                stop(current.video);
              }
              current.video = item;
            }
          }
        });
      }
    };
  })(jQuery, Drupal, once);
