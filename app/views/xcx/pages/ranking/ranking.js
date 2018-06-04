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
    scrollheight: '', /*设置可滚动 高度*/
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
        nickname: '壹贰叁肆伍陆柒捌玖零',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 111111,
      },
      {
        nickname: '贰叁肆伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 222222,
      },
      {
        nickname: '叁肆伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 33333,
      },
      {
        nickname: '壹贰叁肆伍陆柒捌玖零拾',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 44444,
      },
      {
        nickname: '伍陆柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 55555,
      },
      {
        nickname: '陆柒捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 666666,
      },
      {
        nickname: '柒捌玖',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 777777,
      },
      {
        nickname: '捌玖',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 88888,
      },


    ],
    monthlyCharmList: [
      {
        nickname: '零拾佰仟万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 986543,
      },
      {
        nickname: '拾佰仟万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 987653,
      },
      {
        nickname: '佰仟万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 76543,
      },
      {
        nickname: '仟万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 54321,
      },
      {
        nickname: '万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 987653,
      },
      {
        nickname: '亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 654335,
      },
      {
        nickname: '亿万仟佰拾零',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 456776543,
      },
      {
        nickname: '万仟佰拾零',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 454345,
      },


    ],
    charmList: [
      {
        nickname: '玖捌柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 45652345,
      },
      {
        nickname: '捌柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 765433456,
      },
      {
        nickname: '柒陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 9876532,
      },
      {
        nickname: '陆伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 1234356,
      },
      {
        nickname: '伍肆叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 323456,
      },
      {
        nickname: '肆叁贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 6643321,
      },
      {
        nickname: '叁贰壹',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 556678,
      },
      {
        nickname: '贰壹',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 1234532,
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
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 112233,
      },
      {
        nickname: '二三四五六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 223123,
      },
      {
        nickname: '三四五六七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 213424,
      },
      {
        nickname: '四五六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 112314,
      },
      {
        nickname: '五六七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 131425,
      },
      {
        nickname: '六七八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 1314164,
      },
      {
        nickname: '七八九',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 1234212,
      },
      {
        nickname: '八九',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 12344563,
      },


    ],
    monthlyContributeList: [
      {
        nickname: '零十百千万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 1234452,
      },
      {
        nickname: '十百千万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 564323,
      },
      {
        nickname: '百千万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 23456786,
      },
      {
        nickname: '千万亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 45626327,
      },
      {
        nickname: '万亿',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 123456,
      },
      {
        nickname: '亿',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 8762567,
      },
      {
        nickname: '亿万千百十零',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 97635,
      },
      {
        nickname: '万千百十零',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 465678,
      },


    ],
    contributeList: [
      {
        nickname: '九八七六五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '9876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 21,
        userId: 744635,
      },
      {
        nickname: '八七六五四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '876.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 22,
        userId: 1125646,
      },
      {
        nickname: '七六五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '76.54万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 23,
        userId: 1123456,
      },
      {
        nickname: '六五四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '6.54万',
        glory: '/images/glory_1.png',
        onfocus: true,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 24,
        userId: 1332456,
      },
      {
        nickname: '五四三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '5.4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 29,
        userId: 4566735,
      },
      {
        nickname: '四三二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '4万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 25,
        userId: 157753,
      },
      {
        nickname: '三二一',
        avatar_small_url: '/images/room_cover_1.jpg',
        value: '3万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 1,
        age: 26,
        userId: 7642356,
      },
      {
        nickname: '二一',
        avatar_small_url: '/images/room_cover_2.jpg',
        value: '0.8万',
        glory: '/images/glory_1.png',
        onfocus: false,
        province_name: '北京',
        city_name: '北京',
        male: 0,
        age: 34,
        userId: 852332,
      },


    ],
    /*查看个人信息弹出层*/
    hideMask: true,
    hideInfo: true,
    ico_id: '/images/ico_id.png',
    ico_prosecute: '/images/ico_close.png',
    ico_nofocus: '/images/ico_nofocus.png',
    ico_onfocus: '/images/ico_onfocus.png',
    ico_close: '/images/ico_close.png',
    notRoom:true,
    lookInfo: {
      nickname: null,
      avatar_small_url: null,
      value: null,
      glory: null,
      onfocus: false,
      province_name: null,
      city_name: null,
      male: null,
      age: null,
      userId: null,
    },
    /*无网络状态*/
    networkType: true,
    nonetwork: '/images/nonetwork.png',
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
    if (this.data.tabsIdx) {
      this.setData({
        onIdx: idx,
        onItem: idx,
      })
    } else {
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
    } else {
      this.setData({
        curIdx: i,
      })
    }

  },
  /*查看用户信息 根据榜单不同，查找不同列表*/
  lookUserInfo: function (e) {
    let userid = e.currentTarget.dataset.userid || null,
     tabsIdx = this.data.tabsIdx, // 0 为魅力榜 1为贡献榜
     onIdx = this.data.onIdx,
     curIdx = this.data.curIdx,
     lookList="";
    switch (tabsIdx ? onIdx : curIdx) {
      case 0:
        lookList = tabsIdx ? this.data.dailyContributeList : this.data.dailyCharmList;
        break;
      case 1:
        lookList = tabsIdx ? this.data.monthlyContributeList : this.data.dailyCharmList;
        break;
      case 2:
        lookList = tabsIdx ? this.data.contributeList : this.data.charmList;
        break;
    }
    console.log('tabsIdx', tabsIdx, 'onIdx', onIdx, 'curIdx', curIdx, 'userid', userid)
    lookList.forEach((item)=>{
      if (userid==item.userId){
        this.setData({
          lookInfo: item,
          hideMask: false,
          hideInfo: false,
        })
      } 
    })
  }, 

/*如果没有关注，点击关注 */
  getFocus:function(e){
    
    console.log('添加到关注列表')
  }, 

  /*如果在房间，调整到当前房间去*/
  navToTheRoom:function(e) {  
    clearTimeout(timerRoom) 
    let room = e.currentTarget.dataset.room || null;
    if (room){
      console.log('去该用户所在房间')
    }else{
      // 提示当前不在房间
      this.setData({
        notRoom: room,
      }) 
    }
    var timerRoom = setTimeout(() => {
      this.setData({
        notRoom: true,
      })
    }, 1000) 
  },
/*关闭弹窗*/
  closePopup: function (e) {
    this.setData({
      hideMask: true,
      hideInfo: true,
    })
  }, 
  
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    // 获取网络类型
    wx.getNetworkType({
      success: (res) => {
        // 返回网络类型, 有效值：
        // wifi/2g/3g/4g/unknown(Android下不常见的网络类型)/none(无网络)
        let networkType = (res.networkType !== 'none') ? true : false;
        this.setData({
          networkType: networkType,
        })
      }
    })

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