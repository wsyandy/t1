// pages/my_account/my_account.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ico_gold: '/images/ico_gold.png',
    ico_diamond: '/images/ico_diamond.png',
    ico_check: '/images/ico_check.png',
    ico_checked: '/images/ico_checked.png',
    myDiamond:'8888',
    checkedIdx:0,
    topupList:[
      {
        diamond: 60,
        giving: 0,
        gold: 0,
        rmb: 6,
      },
      {
        diamond: 300,
        giving: 0,
        gold: 0,
        rmb: 30,
      },
      {
        diamond: 680,
        giving: 0,
        gold: 0,
        rmb: 68,
      },
      {
        diamond: 1180,
        giving: 140,
        gold: 300,
        rmb: 118,
      },
      {
        diamond: 1980,
        giving: 300,
        gold: 800,
        rmb: 198,
      },
      {
        diamond: 4880,
        giving: 900,
        gold: 1200,
        rmb: 448,
      },
      {
        diamond: 9980,
        giving: 2300,
        gold: 300,
        rmb: 998,
      },
      {
        diamond: 28880,
        giving: 6656,
        gold: 10000,
        rmb: 2888,
      },
    ]
  },
  /*选择充值金额*/
  topupChecked:function(e){
    let index = e.currentTarget.dataset.index;
    this.setData({
      checkedIdx: index
    })
  },
 /*确定充值*/
  topupDiamond: function () {
   
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
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