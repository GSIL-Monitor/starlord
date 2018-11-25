// pages/trip/trip.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabs: ['车找人', '人找车'],
    currentTab: 0,
    contentHeight: 0,
    data: [
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      },
      {
        date: '每天22:15',
        start: '北京市朝阳区这条路这个街道',
        end: '北京市海淀区这条路这个街道',
        price: '128元/人',
      }
    ]
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
})