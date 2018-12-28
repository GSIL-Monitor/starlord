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
    loading_data: true,
    detail: {},
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
    self.setData({loading_data: true});
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
    if (trip_id && user_id) {
      self.setData({
        loading_data: true
      });
      service.driverGetDetailByTripId({ trip_id, user_id }, (success, data) => {
        wx.stopPullDownRefresh();
        self.setData({
          loading_data: false,
          detail: data || {}
        });
      });
    }
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (r) {
    const { user_config } = app.globalData;
    const share_title = (user_config && user_config.docoment && user_config.docoment.share_description) ? user_config.docoment.share_description : null;
    const { trip_id, user_id } = self.data;

    return {
      title: share_title,
      path: `/pages/driverPublishShare/driverPublishShare?trip_id=${trip_id}&user_id=${user_id}`,
      imageUrl: '../../images/address.png'
    };
  },

  makeCall: function (e) {
    const { phone } = e.currentTarget.dataset;
    wx.makePhoneCall({
      phoneNumber: phone,
    });
  },
  shareTopGroup: function () {
    
  }
})