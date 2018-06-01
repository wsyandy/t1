// pages/my_account/my_account.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: { 
    icon_diamond: '/images/icon_diamond.png',  
    tabsIdx: 0,
    tabs: ["收到","送出"],
    scrollheight: '',/*设置可滚动 高度*/
    giftgetList: [
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨王晓馨王晓馨王晓馨王晓馨',
        gift_ico: '/images/gift01.png', 
        gift_tit: '西瓜', 
        gift_price: 20,
        time: '2018-01-29 20:28:00',
        num: 2,
      },
      {
        avatar: '/images/room_cover_2.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift02.png',
        gift_tit: '烧烤',
        gift_price: 30,
        time: '2018-01-29 20:29:00',
        num: 3,
      },
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift03.png',
        gift_tit: '雪糕',
        gift_price: 10,
        time: '2018-01-29 20:28:00',
        num: 4,
      },
      {
        avatar: '/images/room_cover_2.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift04.png',
        gift_tit: '龙虾',
        gift_price: 40,
        time: '2018-01-29 20:29:00',
        num: 1,
      },
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨王晓馨王晓馨王晓馨王晓馨',
        gift_ico: '/images/gift01.png',
        gift_tit: '西瓜',
        gift_price: 20,
        time: '2018-01-29 20:28:00',
        num: 2,
      },
      {
        avatar: '/images/room_cover_2.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift02.png',
        gift_tit: '烧烤',
        gift_price: 30,
        time: '2018-01-29 20:29:00',
        num: 3,
      },
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift03.png',
        gift_tit: '雪糕',
        gift_price: 10,
        time: '2018-01-29 20:28:00',
        num: 4,
      },
    ],
    giftSendList: [
      
      {
        avatar: '/images/room_cover_2.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift04.png',
        gift_tit: '龙虾',
        gift_price: 40,
        time: '2018-01-29 20:29:00',
        num: 1,
      },
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨王晓馨王晓馨王晓馨王晓馨',
        gift_ico: '/images/gift01.png',
        gift_tit: '西瓜',
        gift_price: 20,
        time: '2018-01-29 20:28:00',
        num: 2,
      },
      {
        avatar: '/images/room_cover_2.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift02.png',
        gift_tit: '烧烤',
        gift_price: 30,
        time: '2018-01-29 20:29:00',
        num: 3,
      },
      {
        avatar: '/images/room_cover_1.jpg',
        giver: '王晓馨',
        gift_ico: '/images/gift03.png',
        gift_tit: '雪糕',
        gift_price: 10,
        time: '2018-01-29 20:28:00',
        num: 4,
      },
    ]
  },
  /*选择充值金额*/
  mygiftTabs: function (e) {
    let index = e.currentTarget.dataset.index;
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

  }
})