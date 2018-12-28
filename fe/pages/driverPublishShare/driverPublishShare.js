const service = require('../../utils/service');
const config = require('../../utils/config');
const app = getApp();
let self;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    trip_id: null,
    user_id: null,
    detail: {},
    loading_data: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    self = this;
    wx.showShareMenu({
      withShareTicket: true
    });
    options = options || {};
    this.setData({
      trip_id: options.trip_id || null,
      user_id: options.user_id || null,
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    self.setData({
      loading_data: true
    });
    wx.startPullDownRefresh();
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    const { trip_id, user_id } = self.data;
    const { wx_config } = app.globalData;
    const { shareTicket } = wx_config;
    if (!trip_id || !user_id || !shareTicket) {
      wx.showToast({
        title: '页面参数不正确',
        icon: null
      });
      wx.stopPullDownRefresh();
      return;
    }
    self.setData({loading_data: true});
    const params = {
      trip_id, user_id, shareTicket,
      trip_type: 0
    };
    const callback = (success, data) => {
      wx.stopPullDownRefresh();
      self.setData({ loading_data: false });
      if (success) {
        let tags = [];
        config.driver_tags.map(tag => {
          if (data[tag.value] == 1) {
            tags.push(tag.label);
          }
        });
        data.tags = tags;
        if (data.user_info) {
          data.user_info = JSON.parse(data.user_info);
        }
        self.setData({
          detail: data || {}
        });
      }
    }
    service.getTripDetailInSharePage(params, callback);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    const { user_config } = app.globalData;
    const share_title = (user_config && user_config.docoment && user_config.docoment.share_description) ? user_config.docoment.share_description : null;
    const { trip_id, user_id } = self.data;

    return {
      title: share_title,
      path: `/pages/driverPublishShare/driverPublishShare?trip_id=${trip_id}&user_id=${user_id}`,
      imageUrl: '../../images/address.png'
    };
  },

  nativeBack: () => {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },
  makeCall: function (e) {
    const { phone } = e.currentTarget.dataset;
    wx.makePhoneCall({
      phoneNumber: phone,
    });
  },
})