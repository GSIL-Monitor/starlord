// pages/search/search.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tags: ['无烟车', '接送到家', '可捎货', '会开车优先', '健谈优先', '全程高速', '可带宠物', '空调开放'],
    tabs: ['车找人', '人找车'],
    currentTab: 0,
    contentHeight: 0,
    date: null,
    time: null,
    startLocation: null,
    endLocation: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
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
  bindTabChange: function (e) {
    var current = e.detail.current;
    this.setData({
      currentTab: current
    });
  },
  navTabClick: function (e) {
    this.setData({
      currentTab: e.currentTarget.id
    });
  },
  onSeachFindCustomer: function (e) {
    wx.navigateTo({
      url: '/pages/findCustomerList/findCustomerList',
    })
  },
  chooseLocation: function (e) {
    var locationType = e.currentTarget.dataset.location;
    var that = this;
    wx.chooseLocation({
      success: function(res) {
        console.error(res);
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
      date: e.detail.value
    });
  },
  bindTimeChange: function (e) {
    this.setData({
      time: e.detail.value
    });
  },
})