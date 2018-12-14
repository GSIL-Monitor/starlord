// pages/publish/publish.js
const service = require('../../utils/service');
const app = getApp();
let self;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    loading_data: false,
    templates: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    self = this;
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
    this.fetchTemplateData();
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
    this.fetchTemplateData();
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

  fetchTemplateData: () => {
    self.setData({
      loading_data: true
    });
    service.getTemplateList((success, data) => {
      self.setData({
        loading_data: false,
        templates: data || []
      });
      wx.stopPullDownRefresh();
    });
  },

  goPage: function (e) {
    const { page, tripid } = e.currentTarget.dataset;
    let url = `/pages/${page}/${page}`;
    if (tripid) {
      url = `${url}?trip_id=${tripid}`
    }
    wx.navigateTo({
      url
    })
  },

  onDeleteTemplate: function (e) {
    const { tripid, triptype } = e.currentTarget.dataset;
    wx.showModal({
      title: '删除模板',
      content: '您确定删除该模板吗？',
      success(res) {
        if (res.confirm) {
          wx.showLoading({mask: true});
          service.deleteTemplate({
            trip_id: tripid,
            trip_type: triptype
          }, (success, data) => {
            wx.hideLoading();
            if (success) {
              self.fetchTemplateData();
            }
          });
        }
      }
    })
  }
})