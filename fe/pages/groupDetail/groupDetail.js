const service = require('../../utils/service');
const config = require('../../utils/config');
const app = getApp();
let self;
Page({
  /**
   * 页面的初始数据
   */
  data: {
    params: {},
    loading_data: false,
    detail: {},
    tabs: ['车找人', '人找车'],
    currentTab: 0,
    driverTrips: [],
    passengerTrips: [],
    driver_loading: false,
    passenger_loading: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    self = this;
    self.setData({
      params: options
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
    self.setData({
      loading_data: true
    });
    service.getDetailByGroupId(self.data.params, (success, data) => {
      wx.stopPullDownRefresh();
      self.setData({
        loading_data: false,
        detail: success ? data : {}
      });
    });
    this.loadTripsData();
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
    return app.appShare();
  },

  navTabClick: function (e) {
    this.setData({
      currentTab: e.currentTarget.id
    });
    this.loadTripsData();
  },

  loadTripsData: () => {
    const { group_id } = self.data.params;
    if (self.data.currentTab == 0) {
      self.setData({
        driver_loading: true
      });
      service.driverGetListByGroupId({ group_id }, (success, data) => {
        self.setData({
          driver_loading: false,
          driverTrips: data
        });
      });
    } else if (self.data.currentTab == 1) {
      self.setData({
        passenger_loading: true
      });
      service.passengerGetListByGroupId({ group_id }, (success, data) => {
        self.setData({
          passenger_loading: false,
          passengerTrips: data
        });
      });
    }
  },

  exitGroup: () => {
    wx.showModal({
      title: '退出拼车群',
      content: '您确定删退出该群吗？',
      success(res) {
        if (res.confirm) {
          wx.showLoading({ mask: true });
          service.exitGroup({
            group_id: self.data.params.group_id,
          }, (success, data) => {
            wx.hideLoading();
            if (success) {
              wx.reLaunch({
                url: '/pages/index/index',
              })
            }
          });
        }
      }
    })
  },
  onEditNotice: () => {
    wx.navigateTo({
      url: `/pages/groupNotice/groupNotice?group_id=${self.data.params.group_id}`,
    })
  },

  groupOwnerTip: () => {
    wx.navigateTo({
      url: `/pages/groupOwnerTip/groupOwnerTip?group_id=${self.data.params.group_id}`,
    })
  },
  makeCall: function (e) {
    const { phone } = e.currentTarget.dataset;
    wx.makePhoneCall({
      phoneNumber: phone,
    });
  },
})