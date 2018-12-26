//app.js
const service = require('/utils/service');
const config = require('/utils/config');
App({
  onLaunch: function (r) {
    service.userConfig(this);
  },
  onShow: function (r) {
    this.globalData.wx_config = r || {};
    // console.error(r,'xxxx');
    // if (r.shareTicket && config.share_pages.indexOf(r.path) > -1) {
    //   service.getAndUploadGroup(r.shareTicket);
    // }
  },
  globalData: {
    is_login: false,
    profile: null,
    user_config: null,
    wx_config: {},
  }
})