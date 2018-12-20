//app.js
const service = require('/utils/service');
const config = require('/utils/config');
App({
  onLaunch: function (r) {
    console.error('xx', r);
    service.userConfig(this);
  },
  onShow: function (r) {
    console.error(r);
    if (r.shareTicket && config.share_pages.indexOf(r.path) > -1) {
      service.getAndUploadGroup(r.shareTicket);
    }
  },
  globalData: {
    is_login: false,
    profile: null,
    userConfig: null,
  }
})