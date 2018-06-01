// pages/ranking/ranking.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabsIdx: 0,
    tabs: ["魅力榜", "贡献榜"],
    topTabs: ['日榜', '月榜', '总榜'],
    toView: '',
    curIdx: 0, /* scroll_tabs 索引值 与 curItem 对应 */
    curItem: 0, /*swiper-item 索引值 与 curIdx 对应*/
    stars: '/images/tag_stars.png',
    scrollheight: '',/*设置可滚动 高度*/
    /*魅力榜*/
    charm: [], 
    /*贡献榜*/
    contribute: [], 
  },

  rankTabs: function (e) {
    let index = e.currentTarget.dataset.index;
    this.setData({
      tabsIdx: index
    })
  },

  /* 滚动选项卡点击 Swiper 跳转到对应显示页 */
  tabSelect: function (e) {
    let idx = e.currentTarget.dataset.idx 
    this.setData({
      curIdx: idx,
      curItem: idx, 
    })
  },
  /* Swiper 内容改变时，滚动选项卡跳转到对应位置 */
  tabSwiperChange: function (e) {
    let i = e.detail.current 
    this.setData({
      curIdx: i, 
    })
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 解决 swiper 自适应高度问题 200为顶部head和tabs高度
    this.setData({
      scrollheight: app.globalData.windowHeight - (app.globalData.windowWidth / 750 * 240)
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