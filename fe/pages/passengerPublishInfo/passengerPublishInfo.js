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
    loading_data: false,
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
    this.loadData();
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
      service.passengerGetDetailByTripId({ trip_id, user_id }, (success, data) => {
        wx.stopPullDownRefresh();
        if (success && data) {
          let tags = [];
          config.passenger_tags.map(tag => {
            if (data[tag.value] == 1) {
              tags.push(tag.label);
            }
          });
          data.tags = tags;
          if (data.user_info) {
            data.user_info = JSON.parse(data.user_info);
          }
        }
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
  onShareAppMessage: function () {

  },

  loadData: () => {
    wx.startPullDownRefresh();
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