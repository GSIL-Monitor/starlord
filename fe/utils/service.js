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

/** 获取用户profile */
const getProfile = (app, myCallback) => {
  const callback = (success, data) => {
    if (success) {
      app.globalData.profile = data;
    }
    if (myCallback) {
      myCallback(success, data);
    }
  }
  request('user/getProfile', null, callback);
}

/** 用户配置信息 */
const userConfig = (app) => {
  getProfile(app);
  // !TODO从本地获取config判断expire信息
  const callback = (success, data) => {
    if (!success) return;
    wx.setStorageSync(config.storage_userconfig, data);
    app.globalData.user_config = data;

    // 刷新当前页面
    const pages = getCurrentPages();
    if (pages.length > 0) {
      pages[pages.length - 1].onShow();
    }
  }
  request('user/config', {}, callback);
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

/** 更改手机号码 */
const updateUserPhone = (data, success) => {
  request('user/updateUserPhone', data, success);
}

/** 更改车辆信息 */
const updateUserCar = (data, success) => {
  request('user/updateUserCar', data, success);
}

/** 获取发布模板 */
const getTemplateList = (callback) => {
  request('trip/getTemplateList', null, callback);
}
/** 删除发布模板 */
const deleteTemplate = (data,callback) => {
  request('trip/deleteTemplate', data, callback);
}

/** 获取群列表(包含群详情) */
const getGroupListByUserId = (callback) => {
  request('group/getListByUserId', null, callback);
}
const getDetailByGroupId = (data, callback) => {
  request('group/getDetailByGroupId', data, callback);
}
const exitGroup = (data, callback) => {
  request('group/exitGroup', data, callback);
}
/** 获取分享群的信息 */
const getTripDetailInSharePage = (data, callback) => {
  const successCb = (r) => {
    if (r.errMsg == 'getShareInfo:ok') {
      const params = {
        iv: r.iv,
        encryptedData: r.encryptedData,
        user_id: data.user_id,
        trip_id: data.trip_id,
        trip_type: data.trip_type,
      };
      request('trip/getTripDetailInSharePage', params, callback);
    }
  }
  wx.getShareInfo({ shareTicket: data.shareTicket, success: successCb });
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
const driverGetDetailByTripId = (data, success) => {
  request('trip/driverGetDetailByTripId', data, success);
}
const driverGetMyList = (success) => {
  request('trip/driverGetMyList', null, success);
}
const driverDeleteMy = (data, success) => {
  request('trip/driverDeleteMy', data, success);
}
const driverGetListByGroupId = (data, success) => {
  request('trip/driverGetListByGroupId', data, success);
}
const driverSearch = (data, callback) => {
  request('search/all', { ...data, trip_type: 0 }, callback);
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
const passengerGetDetailByTripId = (data, success) => {
  request('trip/passengerGetDetailByTripId', data, success);
}
const passengerGetMyList = (success) => {
  request('trip/passengerGetMyList', null, success);
}
const passengerDeleteMy = (data, success) => {
  request('trip/passengerDeleteMy', data, success);
}
const passengerGetListByGroupId = (data, success) => {
  request('trip/passengerGetListByGroupId', data, success);
}
const passengerSearch = (data, callback) => {
  request('search/all', { ...data, trip_type: 1 }, callback);
}

module.exports = {
  request,
  login,
  getProfile,
  updateUserCar,
  userConfig,
  userCompleteUser,
  updateUserPhone,
  getTemplateList,
  deleteTemplate,
  getGroupListByUserId,
  getDetailByGroupId,
  getTripDetailInSharePage,
  exitGroup,
  driverPublish,
  driverSave,
  driverGetDetailByTripId,
  driverGetMyList,
  driverDeleteMy,
  driverGetListByGroupId,
  driverSearch,
  passengerPublish,
  passengerSave,
  passengerGetDetailByTripId,
  passengerGetMyList,
  passengerDeleteMy,
  passengerGetListByGroupId,
  passengerSearch,
}