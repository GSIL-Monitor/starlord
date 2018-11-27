//app.js
App({
  onLaunch: function () {
    wx.login({
      success(r){
        console.error(r);
      }
  })
 
  },
  globalData: {
  }
})