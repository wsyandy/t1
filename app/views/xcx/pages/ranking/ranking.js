// pages/ranking/ranking.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    /*顶部选项卡*/
    tabsIdx: 0,
    rankTabs: ["魅力榜", "贡献榜"],
    topTabs: ['日榜', '月榜', '总榜'],
    toView: '',
    scrollheight: '',/*设置可滚动 高度*/
    /*性别*/
    ico_male: '/images/ico_male.png',
    ico_female: '/images/ico_female.png',
    /*前三名*/
    rank_first: '/images/rank_first.png',
    rank_second: '/images/rank_second.png',
    rank_third: '/images/rank_third.png',
    /*魅力榜*/
    curIdx: 0, /* scroll_tabs 索引值 与 curItem 对应 */
    curItem: 0, /*swiper-item 索引值 与 curIdx 对应*/
    dailyCharmList: [
      {
        nickname: '壹贰叁肆伍陆柒捌玖零拾佰仟万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '贰叁肆伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '叁肆伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '肆伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '陆柒捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
    monthlyCharmList: [
      {
        nickname: '零拾佰仟万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '拾佰仟万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '佰仟万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '仟万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '亿万仟佰拾零',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '万仟佰拾零',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
    charmList: [
      {
        nickname: '玖捌柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '捌柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
    /*贡献榜*/
    onIdx: 0,
    onItem: 0,
    dailyContributeList: [
      {
        nickname: '一二三四五',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '二三四五六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '三四五六七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '四五六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '五六七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
    monthlyContributeList: [
      {
        nickname: '零十百千万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '十百千万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '百千万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '千万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '亿万千百十零',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '万千百十零',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
    contributeList: [
      {
        nickname: '九八七六五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 21,
      },
      {
        nickname: '八七六五四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 22,
      },
      {
        nickname: '七六五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 23,
      },
      {
        nickname: '六五四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 24,
      },
      {
        nickname: '五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 29,
      },
      {
        nickname: '四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 25,
      },
      {
        nickname: '三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        male: 1,
        age: 26,
      },
      {
        nickname: '二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        male: 0,
        age: 34,
      },


    ],
  },
 /*顶部选项卡*/
  rankingTabs: function (e) {
    let index = e.currentTarget.dataset.index;
    this.setData({
      tabsIdx: index
    })
  },

  /* 日、月、总选项卡点击 Swiper 跳转到对应显示页 */
  tabSelect: function (e) {
    let idx = e.currentTarget.dataset.idx
    /*判断 this.data.tabsIdx 的值是魅力榜还是贡献榜  0 为魅力 1为贡献*/
    if (this.data.tabsIdx){
      this.setData({
        onIdx: idx,
        onItem: idx,
      })
    }else{
      this.setData({
        curIdx: idx,
        curItem: idx,
      })
    }
   
  },
  /* Swiper 内容改变时，日日、月、总选项卡跳转到对应位置 */
  tabSwiperChange: function (e) {
    let i = e.detail.current;
    /*判断 this.data.tabsIdx 的值是魅力榜还是贡献榜  0 为魅力 1为贡献*/

    if (this.data.tabsIdx) {
      this.setData({
        onIdx: i,
      })
    }else{
      this.setData({
        curIdx: i,
      })
    }
   
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 解决 swiper 自适应高度问题 200为顶部head和tabs高度
    this.setData({
      scrollheight: app.globalData.windowHeight - (app.globalData.windowWidth / 750 * 220)
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