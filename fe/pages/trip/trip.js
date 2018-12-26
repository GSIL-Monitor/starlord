// pages/trip/trip.js
const service = require('../../utils/service');
const app = getApp();
let self;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabs: ['车找人', '人找车'],
    currentTab: 0,
    contentHeight: 0,
    loading_passenger: true,
    loading_driver: true,
    driverTrips: [],
    passengerTrips: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    self = this;
    wx.getSystemInfo({
      success: function (res) {
        self.setData({
          contentHeight: res.windowHeight - res.windowWidth / 750 * 68
        });
      }
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
      loading_passenger: true,
      loading_driver: true,
    });
    self.loadData();
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

  passengerGetMyList: () => {
    self.setData({ loading_passenger: true });
    service.passengerGetMyList((success, data) => {
      self.setData({ loading_passenger: false });
      if (success) {
        self.setData({
          passengerTrips: data
        });
      }
    });
  },
  driverGetMyList: () => {
    self.setData({ loading_driver: true });
    service.driverGetMyList((success, data) => {
      self.setData({ loading_driver: false });
      if (success) {
        self.setData({
          driverTrips: data
        });
      }
    });
  },

  loadData: () => {
    const { currentTab } = self.data;
    if (currentTab == 0) {
      self.driverGetMyList();
    } else {
      self.passengerGetMyList();
    }
  },

  bindTabChange: function (e) {
    var current = e.detail.current;
    this.setData({
      currentTab: current
    });
    this.loadData();
  },
  navTabClick: function (e) {
    this.setData({
      currentTab: e.currentTarget.id
    });
    this.loadData();
  },
  onCancelTrip: (e) => {
    const { tripid, triptype } = e.target.dataset;
    wx.showModal({
      title: '删除行程',
      content: '您确定删除该行程吗？',
      success(res) {
        if (res.confirm) {
          wx.showLoading({mask: true});
          if (triptype == 'driver') {
            service.driverDeleteMy({
              trip_id: tripid,
            }, (success) => {
              wx.hideLoading();
              if (success) {
                wx.showToast({
                  title: '行程已删除',
                });
                self.driverGetMyList();
              }
            });
          } else if (triptype == 'passenger') {
            service.passengerDeleteMy({
              trip_id: tripid,
            }, (success) => {
              wx.hideLoading();
              if (success) {
                wx.showToast({
                  title: '行程已删除',
                });
                self.passengerGetMyList();
              }
            });
          }
        }
      }
    })
  }
})