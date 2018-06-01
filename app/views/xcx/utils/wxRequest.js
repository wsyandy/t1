var Promise = require('../plugins/es6-promise.js')
var configs = require('config.js')
function wxPromisify(fn) {
  return function (obj = {}) {
    return new Promise((resolve, reject) => {
      obj.success = function (res) {
        //成功
        resolve(res)
      }
      obj.fail = function (res) {
        //失败
        reject(res)
      }
      fn(obj)
    })
  }
}
//无论promise对象最后状态如何都会执行
Promise.prototype.finally = function (callback) {
  let P = this.constructor;
  return this.then(
    value => P.resolve(callback()).then(() => value),
    reason => P.resolve(callback()).then(() => { throw reason })
  );
};
/**
 * 微信请求get方法
 * url
 * data 以对象的格式传入
 */
function getRequest(url, data = {}, judge = false) {
  var getRequest = wxPromisify(wx.request)
  data.version_number = configs.version_number
  data.sid = data.sid ? data.sid : wx.getStorageSync('sid')

  return getRequest({
    url: judge ? configs.config.server_domain + url : configs.config.server_domain + 'xcx/' + url,
    method: 'GET',
    data: data,
    header: {
      'Content-Type': 'application/json'
    }
  })
}

/**
 * 微信请求post方法封装
 * url
 * data 以对象的格式传入
 */
function postRequest(url, data = {}, judge = false) {
  var postRequest = wxPromisify(wx.request)
  data.version_number = configs.version_number
  data.sid = data.sid ? data.sid : wx.getStorageSync('sid')

  return postRequest({
    url: judge ? configs.config.server_domain + url : configs.config.server_domain + 'xcx/' + url,
    method: 'POST',
    data: data,
    header: {
      "content-type": "application/x-www-form-urlencoded"
    },
  })
}

module.exports = {
  postRequest: postRequest,
  getRequest: getRequest
}