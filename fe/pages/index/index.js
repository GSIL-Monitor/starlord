//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    list: [
      {
        id: 1,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 2,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 3,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 4,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 5,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 5,
        avatar: null,
        title: "拼车标题",
      },
      {
        id: 5,
        avatar: null,
        title: "拼车标题",
      }
    ]
  },
  onLoad: function (r) {
    wx.showShareMenu({
      withShareTicket: true
    });
  },
  onPullDownRefresh: function () {
    setTimeout(wx.stopPullDownRefresh, 2000);
  }
})
