const wxApi = require("/utils/wxApi.js");
const request = require("/utils/wxRequest.js");
const Utils = require("/utils/util.js");
const updateManager = wx.getUpdateManager()
updateManager.onCheckForUpdate(function (res) {
  // 请求完新版本信息的回调
  if (res.hasUpdate) {
    updateManager.onUpdateReady(function () {
      wx.showModal({
        title: '版本更新',
        content: '新版本已经准备好，是否重启应用？',
        success: function (res) {
          if (res.confirm) {
            // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
            updateManager.applyUpdate()
          }
        }
      })
    })

    updateManager.onUpdateFailed(function () {
      // 新的版本下载失败
      wx.showModal({
        title: '版本更新',
        content: '新版本下载失败，请您清除小程序缓存重新搜索小程序进入',
      })
    })
  }
})

App({
  addListener: function (callback) {
    this.callback = callback;
  },
  setChangedData: function (data) {
    this.data = data;
    if (this.callback != null) {
      this.callback(data);
    }
  },

  getUserInfo: function (userinfo, callback) {
    var _this = this
    let wxLogin = wxApi.wxLogin()
    wxLogin().then(res => {
      if (wx.getStorageSync('userInfo')) {
        callback(wx.getStorageSync('userInfo'))
      } else {
        if (userinfo.detail.errMsg == 'getUserInfo:ok') {
          //用户同意授权
          let wxGetSystemInfo = wxApi.wxGetSystemInfo()
          wxGetSystemInfo().then(e => {

            let data = {
              encryptedData: userinfo.detail.encryptedData,
              code: res.code,
              iv: userinfo.detail.iv,
              system: e.system
            }
            return request.postRequest('users/register', data)
          })
            .then(res => {
              Utils.log(`返回状态：${res.data.error_code},状态理由：${JSON.stringify(res.data.error_reason)}`)
              if (res.data.error_code != 0) {
                wx.showToast({
                  title: '注册失败',
                  icon: 'none',
                  duration: 2000
                })
                return;
              }

              let data = {
                sid: res.data.sid
              }
              return request.postRequest('users/detail', data)
            })
            .then(res => {
              Utils.log(`user/detail返回状态：${res.data.error_code}`)
              if (res.data.error_code != 0) {
                wx.showToast({
                  title: '查询失败',
                  icon: 'none',
                  duration: 2000
                })
                return;
              }
              _this.globalData.userInfo = res.data.user
              _this.globalData.sid = res.data.user.sid
              wx.setStorageSync('sid', res.data.user.sid)
              wx.setStorageSync('userInfo', res.data.user)
              callback(res.data.user)
            })
        } else {
          //用户拒绝授权
          wx.showModal({
            title: "无法完成登录",
            content: "[XXXX]小程序需要获取你的用户资料，用于登录。请重新登录，并确保允许小程序获取用户资料。",
            showCancel: false
          })
          callback(null)
        }
      }

    })
    // .finally(function (res) {})
  },

  globalData: {
    userInfo: wx.getStorageSync('userInfo') ? wx.getStorageSync('userInfo') : null,
    sid: wx.getStorageSync('sid') ? wx.getStorageSync('sid') : null,
  }
})