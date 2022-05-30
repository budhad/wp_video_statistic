(function($) {

  const AppStatistic = {
    init: function () {
      this.registerVideoEvents();
    },
    getFormatDate: function(date = new Date()) {
      let result = date.getFullYear().toString();
      result += '-' + (date.getMonth()+1 < 10 ? '0' : '');
      result += date.getMonth()+1;
      result += '-' + (date.getDate() < 10 ? '0' : '');
      result += date.getDate().toString();
      return result;
    },
    registerVideoEvents: function() {
      const that = this;
      let $video = $('figure');

      $video.each(function(){
        if ( ! $(this).find('video').length ) return;
        
        const date = new Date();
        const dateStr = that.getFormatDate(date);
        const stat = {
          stat_action:'',
          url_page: window.location.pathname,
          url_file: $(this).attr('src'),
          value: JSON.stringify({ time : this.currentTime}),
          date: dateStr
        }
        // начинается воспроизведение медиа
        $(this).find('video').on('playing', function(){
          stat.stat_action = 'playing';
          that.sendStat(stat);
        })
        // воспроизведение завершено.
        $(this).find('video').on('ended', function(){
          stat.stat_action = 'ended';
          that.sendStat(stat);
        })
        // воспроизведение приостановлено
        $(this).find('video').on('pause', function(){
          stat.stat_action = 'pause';
          that.sendStat(stat);
        })
      })
    },
    sendStat: function(stat = {}) {

      if ( !Object.keys(stat).length ) return;

      let data = {
        action: 'statistic',
        stat_action: stat.stat_action,
        nonce: statistic.nonce,
        user: statistic.user,
        url_page: stat.url_page,
        url_file: stat.url_file,
        value: stat.value,
        date: stat.date
      };

      data = Object.assign(data, stat);

      $.ajax({
          type: "POST",
          url: statistic.url,
          data: data,
          success: function(response) {
              if (response.error) {
                  console.log(response.error);
              } else if (response.result) {
//                   console.log(data.result);
              }
          }
      });
    }
  };
  AppStatistic.init();
  
})(jQuery);
