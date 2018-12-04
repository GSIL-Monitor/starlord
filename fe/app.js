//app.js
const service = require('/utils/service');
App({
  onLaunch: function (r) {
    service.userConfig(this);
  },
  onShow: function (r) {
    if (r.shareTicket) {
      service.getAndUploadGroup(r.shareTicket);
    }
  },
  globalData: {
  }
})