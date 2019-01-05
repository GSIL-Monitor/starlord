/**
 * 所有请求
 */
const config = require('./config');
let app;

/** 重启当前页面 */
const reLaunchCurrentPage = () => {
  const pages = getCurrentPages();
  if (pages.length > 0) {
    const currPage = pages[pages.length - 1];
    const urlParams = Object.keys(currPage.options).map(key => {
      return [key, currPage.options[key]].join('=');
    }).join('&');
    wx.reLaunch({
      url: `/${currPage.route}?${urlParams}`,
    })
  }
}

/**
 * 通用request
 * uri: string | required | 不包含host如登录：common/login
 * data: json | required | request的数据
 * callback: function | required | 请求的返回函数，不管请求是否正确都调用callback
 * myOptions: json | optional | 自定义request 的option，跟默认的options做merge
 */
const request = (uri, data, callback, myOptions = {}) => {
  console.debug(`http request`, uri, data);
  const ticket = wx.getStorageSync(config.storage_ticket);
  const defaultCallBack = () => {
    console.warn(`${uri}:无callback请求`);
  }

  if (!app.globalData.app_init && ['common/login', 'user/config'].indexOf(uri) == -1) return;

  // callback 返回2个参数，第一个参数为是否返回success，第二个参数为返回数据
  callback = callback || defaultCallBack;
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
          app.globalData.app_init = false;
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
        app.globalData.app_init = true;
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
const userConfig = (myApp) => {
  app = myApp;

  const callback = (success, data) => {
    if (!success) return;
    app.globalData.app_init = true;
    app.globalData.user_config = data;

    // system maintain
    if (data.switch && data.switch['9999']) {
      wx.reLaunch({
        url: '/pages/9999/9999',
      });
      return;
    } else {
      const pages = getCurrentPages();
      if (pages.length > 0) {
        const currPage = pages[pages.length - 1];
        if (currPage.route == 'pages/9999/9999') {
          wx.reLaunch({
            url: '/pages/index/index',
          });
          return;
        }
      }
    }

    // 刷新当前页面
    reLaunchCurrentPage();
  }
  request('user/config', {}, callback);
}

/** 获取用户profile */
const getProfile = (app, callback) => {
  request('user/getProfile', null, (success, data) => {
    if (success) {
      app.globalData.profile = data;
    }
    if (callback) {
      callback(success, data);
    }
  });
}

/** 上传用户信息 */
const userCompleteUser = (detail, app, page, callback) => {
  callback = callback || (() => {});
  if (detail.errMsg != 'getUserInfo:ok') {
    wx.showToast({
      title: '无法获取用户信息',
      icon: 'none'
    });
    callback(false);
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
    request('user/completeUser', data, callback);
  }
}

/** 更改手机号码 */
const updateUserPhone = (data, callback) => {
  request('user/updateUserPhone', data, callback);
}

/** 更改车辆信息 */
const updateUserCar = (data, callback) => {
  request('user/updateUserCar', data, callback);
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
const updateNotice = (data, callback) => {
  request('group/updateNotice', data, callback);
}
//置顶
const topOneTrip = (data, callback) => {
  request('group/topOneTrip', data, callback);
}
const unTopOneTrip = (data, callback) => {
  request('group/unTopOneTrip', data, callback);
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
const driverPublish = (data, callback) => {
  request('trip/driverPublish', data, callback);
}
const driverSave = (data, callback) => {
  request('trip/driverSave', data, callback);
}
const driverGetDetailByTripId = (data, callback) => {
  request('trip/driverGetDetailByTripId', data, (success, responseData) => {
    if (success && responseData) {
      let tags = [];
      config.driver_tags.map(tag => {
        if (responseData[tag.value] == 1) {
          tags.push(tag.label);
        }
      });
      responseData.tags = tags;
      if (responseData.user_info) {
        responseData.user_info = JSON.parse(responseData.user_info);
      }
    }
    callback(success, responseData);
  });
}
const driverGetMyList = (callback) => {
  request('trip/driverGetMyList', null, callback);
}
const driverDeleteMy = (data, callback) => {
  request('trip/driverDeleteMy', data, callback);
}
const driverGetListByGroupId = (data, callback) => {
  request('trip/driverGetListByGroupId', data, (success, responseData) => {
    if (success && responseData && responseData.length > 0) {
      responseData = responseData.map(item => {
        let tags = [];
        config.driver_tags.map(tag => {
          if (item[tag.value] == 1) {
            tags.push(tag.label);
          }
        });
        item.tags = tags;
        if (item.user_info) {
          item.user_info = JSON.parse(item.user_info);
        }
        return item;
      });
    }
    callback(success, responseData);
  });
}
const driverSearch = (data, callback) => {
  request('search/all', { ...data, trip_type: 0 }, callback);
}

/**
 * 人找车发布、保存
 */
const passengerPublish = (data, callback) => {
  request('trip/passengerPublish', data, callback);
}
const passengerSave = (data, callback) => {
  request('trip/passengerSave', data, callback);
}
const passengerGetDetailByTripId = (data, callback) => {
  request('trip/passengerGetDetailByTripId', data, (success, responseData) => {
    if (success && responseData) {
      let tags = [];
      config.passenger_tags.map(tag => {
        if (responseData[tag.value] == 1) {
          tags.push(tag.label);
        }
      });
      responseData.tags = tags;
      if (responseData.user_info) {
        responseData.user_info = JSON.parse(responseData.user_info);
      }
    }
    callback(success, responseData);
  });
}
const passengerGetMyList = (callback) => {
  request('trip/passengerGetMyList', null, callback);
}
const passengerDeleteMy = (data, callback) => {
  request('trip/passengerDeleteMy', data, callback);
}
const passengerGetListByGroupId = (data, callback) => {
  request('trip/passengerGetListByGroupId', data, (success, responseData) => {
    if (success && responseData && responseData.length > 0) {
      responseData = responseData.map(item => {
        let tags = [];
        config.passenger_tags.map(tag => {
          if (item[tag.value] == 1) {
            tags.push(tag.label);
          }
        });
        item.tags = tags;
        if (item.user_info) {
          item.user_info = JSON.parse(item.user_info);
        }
        return item;
      });
    }
    callback(success, responseData);
  });
}
const passengerSearch = (data, callback) => {
  request('search/all', { ...data, trip_type: 1 }, callback);
}

module.exports = {
  reLaunchCurrentPage,
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
  updateNotice,
  topOneTrip,
  unTopOneTrip,
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