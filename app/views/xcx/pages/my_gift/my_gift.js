const request = require('../../utils/wxRequest.js');
const Utils = require('../../utils/util.js');
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    sendPage: 1,
    receivePage: 1,
    receiveTotalPage: 1,
    sendTotalPage: 1,
    icon_diamond: '/images/icon_diamond.png',
    icon_gold: '/images/ico_gold.png',
    tabsIdx: 0,
    tabs: ["收到", "送出"],
    scrollheight: '',/*设置可滚动 高度*/
    giftSendList: [],
    receiveGifts: [],
  },

  mygiftTabs: function (e) {
    let index = e.currentTarget.dataset.index;
    if (index && this.data.giftSendList) {
      this.setData({
        giftSendList: this.data.giftSendList
      })
    } else if (index) {
      this.receiveGift('send', 'giftSendList')
    }
    this.setData({
      tabsIdx: index
    })
  },

  /*进入送礼物的个人主页*/
  navTouserInfo: function () {

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 解决 礼物列表 自适应高度问题 120为顶部head和tabs高度
    this.setData({
      scrollheight: app.globalData.windowHeight - (app.globalData.windowWidth / 750 * 120)
    });
    this.receiveGift('receive', 'receiveGifts')//加载收到的礼物列表
  },
  /**
   * 收到礼物列表
   * receive收到的礼物
   * send送出的礼物
   */
  receiveGift: function (type = 'receive', objName) {
    let _this = this

    if (type == 'receive') {
      if (_this.data.receivePage > _this.data.receiveTotalPage) {
        wx.showToast({
          title: '已经到底了',
          icon: 'none',
          duration: 1500
        })
        return
      }

    } else {
      if (_this.data.sendPage > _this.data.sendTotalPage) {
        wx.showToast({
          title: '已经到底了',
          icon: 'none',
          duration: 1500
        })
        return
      }

    }

    let data = { [type]: type, page: 1 }
    request.postRequest('gift_orders/index', data).then(res => {
      Utils.log(`列表数据:${JSON.stringify(res.data.gift_orders)}`)
      let gifts = []
      if (type == 'receive') {
        _this.data.receiveTotalPage = res.data.total_page
        _this.data.receivePage++
        _this.data.receiveGifts.concat(res.data.gift_orders)
        gifts = _this.data.receiveGifts
      } else {
        _this.data.sendTotalPage = res.data.total_page
        _this.data.sendPage++
        _this.data.giftSendList.concat(res.data.gift_orders)
        gifts = _this.data.giftSendList
      }

      _this.setData({
        [objName]: gifts ? gifts : {}
      })
    })
  },
  /**
   * 初始化数据
   */
  initData: function () {
    if (this.data.tabsIdx) {
      this.data.sendPage = 1
      this.data.sendTotalPage = 1
      this.data.giftSendList = []
      Utils.log(`初始化送出列表`)
    } else {
      this.data.receivePage = 1
      this.data.receiveTotalPage = 1
      this.data.receiveGifts = []
      Utils.log(`初始化收到列表`)
    }
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
    var _this = this
    _this.initData()
    wx.showNavigationBarLoading() //在标题栏中显示加载

    //模拟加载
    setTimeout(function () {
      if (_this.data.tabsIdx) {
        _this.receiveGift('send', 'giftSendList')
        Utils.log(`在送出列表下拉`)
      } else {
        _this.receiveGift('receive', 'receiveGifts')
        Utils.log(`在收到列表下拉`)
      }
      wx.hideNavigationBarLoading() //完成停止加载
      wx.stopPullDownRefresh() //停止下拉刷新
    }, 1800);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if (this.data.tabsIdx) {
      this.receiveGift('send', 'giftSendList')
      Utils.log(`在送出列表触底`)
    } else {
      this.receiveGift('receive', 'receiveGifts')
      Utils.log(`在收到列表触底`)
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})