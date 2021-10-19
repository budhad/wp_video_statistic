(function($) {

  const AppStatistic = {
    init: function () {
      const that = this;
      let videos = [];
      that.registerVideoEvents(that);
      that.sendStat();
      that.getFormatDate();
    },
    getFormatDate: function(date = new Date()) {
      let result = date.getFullYear().toString();
      result += '-' + (date.getMonth()+1 < 10 ? '0' : '');
      result += date.getMonth()+1;
      result += '-' + (date.getDate() < 10 ? '0' : '');
      result += date.getDate().toString();
      return result;
    },
    registerVideoEvents: function(that) {
      let $video = $('figure');

      $video.each(function(){
        console.log( '$(this).find("video").length', $(this).find('video').length );
        if ( ! $(this).find('video').length ) return;
        // начинается воспроизведение медиа
        $(this).find('video').on('playing', function(){
          let date = new Date();
          let dateStr = that.getFormatDate(date);
          let stat = {
            stat_action:'playing',
            url_page: window.location.pathname,
            url_file: $(this).attr('src'),
            value: JSON.stringify({ time : this.currentTime}),
            date: dateStr
          }
        
          that.sendStat(stat);
        })
        // воспроизведение завершено.
        $(this).find('video').on('ended', function(){
          let date = new Date();
          let dateStr = that.getFormatDate(date);
          let stat = {
            stat_action:'ended',
            url_page: window.location.pathname,
            url_file: $(this).attr('src'),
            value: JSON.stringify({ time : this.currentTime}),
            date: dateStr
          }
          that.sendStat(stat);
        })
        // воспроизведение приостановлено
        $(this).find('video').on('pause', function(){
          let date = new Date();
          let dateStr = that.getFormatDate(date);
          let stat = {
            stat_action:'pause',
            url_page: window.location.pathname,
            url_file: $(this).attr('src'),
            value: JSON.stringify({ time : this.currentTime}),
            date: dateStr
          }
          that.sendStat(stat);
        })
      })
    },
    sendStat: function(stat = {}) {

      if ( !Object.keys(stat).length ) return;

      
      let data = {
        action: 'statistic',
        stat_action: stat.stat_action,
        nonce: ajax.nonce,
        user: statistic.user,
        url_page: stat.url_page,
        url_file: stat.url_file,
        value: stat.value,
        date: stat.date
      };

      data = Object.assign(data, stat);

      $.ajax({
          type: "POST",
          url: ajax.url,
          data: data,
          success: function(data) {
              if (data.error) {
                  alert(data.error);
              } else if (data.result) {
                  console.log(data.result);
              }
          }
      });
    }
  };
  AppStatistic.init();
  
})(jQuery);