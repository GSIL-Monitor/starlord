const service = require('../../utils/service');
const app = getApp();
let self;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    is_login: false,
    tags: [
      { value: 'driver_no_smoke', label: '无烟车' },
      { value: 'driver_last_mile', label: '接送到家' },
      { value: 'driver_goods', label: '可捎货' },
      { value: 'driver_need_drive', label: '会开车优先' },
      { value: 'driver_chat', label: '健谈优先' },
      { value: 'driver_highway', label: '全程高速' },
      { value: 'driver_pet', label: '可带宠物' },
      { value: 'driver_cooler', label: '空调开放' },
    ],
    trip_id: null,
    begin_date: null,
    begin_time: null,
    startLocation: null,
    endLocation: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    self = this;
    options = options || {};
    this.setData({
      is_login: app.globalData.is_login,
      trip_id: options.trip_id || null
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

  chooseLocation: function (e) {
    var locationType = e.currentTarget.dataset.location;
    var that = this;
    wx.chooseLocation({
      success: function (res) {
        if (locationType == 'start') {
          that.setData({
            startLocation: res
          });
        } else {
          that.setData({
            endLocation: res
          });
        }
      },
    })
  },
  bindDateChange: function (e) {
    this.setData({
      begin_date: e.detail.value
    });
  },
  bindTimeChange: function (e) {
    this.setData({
      begin_time: e.detail.value
    });
  },

  /**
   * 微信授权获取个人信息
   */
  getUserInfo: (e) => {
    service.userCompleteUser(e.detail, app, self);
  },

  formSubmit: function (e) {
    const { detail } = e;
    const { value, target } = detail;
    const submitType = target.dataset.type;
    console.error(submitType);
    const { trip_id, begin_date, begin_time, startLocation, endLocation } = self.data;
    if (!begin_date) {
      wx.showToast({
        icon: 'none', title: '日期不能为空',
      });
    } else if (!begin_time) {
      wx.showToast({
        icon: 'none', title: '时间不能为空',
      });
    } else if (!startLocation) {
      wx.showToast({
        icon: 'none', title: '请选择始发点',
      });
    } else if (!endLocation) {
      wx.showToast({
        icon: 'none', title: '请选择终点',
      });
    } else {
      const params = {
        trip_id: trip_id,
        begin_date, begin_time,
        start_location_name: startLocation.name,
        start_location_address: startLocation.address,
        start_location_point: `(${startLocation.latitude},${startLocation.latitude})`,
        end_location_name: endLocation.name,
        end_location_address: endLocation.address,
        end_location_point: `(${endLocation.latitude},${endLocation.latitude})`,
        route: value.route,
        price_everyone: value.price_everyone,
        price_total: value.price_total,
        seat_num: value.seat_num,
        tips: value.tips,
      };
      const callback = (success, tripInfo) => {
        if (success) {
          wx.showToast({
            title: '提交成功'
          });
          if (submitType == 'publish') {
            wx.navigateTo({
              url: `/pages/creareFindCarInfo/driverPublishInfo?trip_id=${tripInfo.trip_id}`,
            });
          } else {
            wx.navigateBack({
              delta: 1
            });
          }
        }
      }

      if (submitType == 'publish') {
        service.driverPublish(params, callback);
      } else {
        service.driverSave(params, callback);
      }
    }
  }
})