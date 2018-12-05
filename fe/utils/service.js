/**
 * 所有请求
 */
const config = require('./config');

/**
 * 通用request
 * uri: string | required | 不包含host如登录：common/login
 * data: json | required | request的数据
 * callback: function | required | 请求的返回函数，不管请求是否正确都调用callback
 * myOptions: json | optional | 自定义request 的option，跟默认的options做merge
 */
const request = (uri, data, callback, myOptions = {}) => {
  console.debug(`http request`, uri, data);
  // callback 返回2个参数，第一个参数为是否返回success，第二个参数为返回数据
  const defaultCallBack = () => {
    console.warn(`${uri}:无callback请求`);
  }
  callback = callback || defaultCallBack;
  const ticket = wx.getStorageSync(config.storage_ticket);
  const defaultOptions = {
    url: `${config.host}/${uri}`,
    method: 'POST',
    data: {
      ...data,
      ticket: ticket
    },
    header: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    success (res) {
      const response = res.data;
      console.debug(`http resonse`, uri, response);
      if (response.errno != 0) {
        // 登录失效，重新登录
        if (response.errno == 1004) {
          login(() => {
            request(uri, data, callback, myOptions);
          });
        } else {
          wx.showToast({
            title: response.errmsg || '请求错误，请重试',
            icon: 'none'
          });
        }
        callback(false, response.data);
      } else {
        callback(true, response.data);
      }
    },
    fail () {
      wx.showToast({
        title: '请求错误，请重试',
        icon: 'none'
      });
      callback(false);
    }
  };
  wx.request({
    ...defaultOptions,
    ...myOptions
  })
}
/** 登录 */
const login = (loginCb) => {
  wx.login({
    success(res) {
      const callback = (success, data) => {
        if (!success) return;
        wx.setStorageSync(config.storage_ticket, data.ticket);
        if (loginCb) loginCb(data.ticket);
      }
      if (res.code) {
        request('common/login', { code: res.code }, callback);
      } else {
        wx.showToast({
          title: '登录失败，请重试',
          icon: 'none'
        });
      }
    },
    fail () {
      wx.showToast({
        title: '登录失败，请重试',
        icon: 'none'
      });
    }
  })
}

/** 用户配置信息 */
const userConfig = (app) => {
  // !TODO从本地获取config判断expire信息
  const callback = (success, data) => {
    if (!success) return;
    wx.setStorageSync(config.storage_userconfig, data);
    app.globalData.userConfig = data;
  }
  request('user/config', {}, callback);
}

/** 获取分享群的信息 */
const getAndUploadGroup = (shareTicket) => {
  const success = (r) => {
    if (r.errMsg == 'getShareInfo:ok') {
      request('group/addUser', {
        iv: r.iv,
        encryptedData: r.encryptedData
      });
    }
  }
  wx.getShareInfo({shareTicket,success})
}

/** 上传用户信息 */
const userCompleteUser = (detail, app, page, success) => {
  success = success || (() => {});
  if (detail.errMsg != 'getUserInfo:ok') {
    wx.showToast({
      title: '无法获取用户信息',
      icon: 'none'
    });
    success(false);
  } else {
    const data = {
      rawData: detail.rawData,
      iv: detail.iv,
      signature: detail.signature,
      encryptedData: detail.encryptedData
    };
    app.globalData.is_login = true;
    page.setData({
      is_login: true
    });
    request('user/completeUser', data, success);
  }
}

/**
 * 车找人发布、保存
 */
const driverPublish = (data, success) => {
  request('trip/driverPublish', data, success);
}
const driverSave = (data, success) => {
  request('trip/driverSave', data, success);
}

/**
 * 人找车发布、保存
 */
const passengerPublish = (data, success) => {
  request('trip/passengerPublish', data, success);
}
const passengerSave = (data, success) => {
  request('trip/passengerSave', data, success);
}

module.exports = {
  request,
  login,
  userConfig,
  getAndUploadGroup,
  userCompleteUser,
  driverPublish,
  driverSave,
  passengerPublish,
  passengerSave,
}