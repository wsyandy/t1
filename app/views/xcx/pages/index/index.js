const app = getApp()
Page({
  data: {
    isIos: app.globalData.isIos, /*设备是否为IOS*/
    isIpx: app.globalData.isIpx,/*设备是否为iPhone X*/
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    logo: '/images/logo_hi.png',
    avatarUrl: '',
    topTabs: ['热门', '最新', '开黑', '唱歌', '交友', '电台', '关注', '附近'],
    toView: '',
    curIdx: 0, /* scroll_tabs 索引值 与 curItem 对应 */
    curItem: 0, /*swiper-item 索引值 与 curIdx 对应*/
    stars: '/images/tag_stars.png',
    scrollheight: '',/*设置可滚动 高度*/
    /* 热门 */
    ico_people: '/images/ico_people.png',
    hotList: [
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '听歌的小伙伴',
        room_num: '999139',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '新人交友',
        room_num: '114',
      },
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '王者荣耀开黑',
        room_num: '9',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '聊啊聊啊',
        room_num: '98',
      },
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '情感分析',
        room_num: '999',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '连麦K歌',
        room_num: '8888',
      },
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '听歌的小伙伴',
        room_num: '139',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '新人交友',
        room_num: '114',
      },
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '王者荣耀开黑',
        room_num: '59',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '聊啊聊啊',
        room_num: '98',
      },
      {
        room_cover: '/images/room_cover_1.jpg',
        room_name: '情感分析',
        room_num: '999',
      },
      {
        room_cover: '/images/room_cover_2.jpg',
        room_name: '连麦K歌',
        room_num: '18',
      },
    ],
    /* 最新 */
    lock: '/images/ico_lock.png',
    newList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '头脑大作战',
        room_num: '3',
        setup_time: '刚刚',
        lock: false,
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '小宠爱',
        room_num: '45',
        setup_time: '1分钟前',
        lock: true,
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '新人交友',
        room_num: '11',
        setup_time: '2分钟前',
        lock: false,
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: 'K歌之王',
        room_num: '23',
        setup_time: '3分钟前',
        lock: false,
      },

    ],
    /* 开黑 */
    gameList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '处CP，唱歌，开黑',
        room_num: '3',
        room_tag: '处CP',
        color: '#f79c52'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '王者荣耀开黑',
        room_num: '45',
        room_tag: '王者荣耀',
        color: '#f79c52'
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '刺激战场',
        room_num: '11',
        room_tag: '绝地求生',
        color: '#f79c52'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '迷雾荣耀局金框',
        room_num: '23',
        room_tag: '球球大作战',
        color: '#f79c52'
      },
    ],
    /* 唱歌 */
    singList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '不想说的话',
        room_num: '13',
        room_tag: '连麦',
        color: '#FF6C7D'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '玩星星',
        room_num: '245',
        room_tag: '唱歌',
        color: '#FF6C7D'
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '没心的大猫',
        room_num: '12',
        room_tag: '聊天',
        color: '#FF6C7D'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '喜欢的你',
        room_num: '42',
        room_tag: '交友',
        color: '#FF6C7D'
      },
    ],
    /* 交友 */
    datingList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '处CP，唱歌',
        room_num: '3',
        room_tag: '处CP',
        color: '#726CFF'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '聊天听歌',
        room_num: '45',
        room_tag: '聊天',
        color: '#726CFF'
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '陪玩',
        room_num: '11',
        room_tag: '聊天',
        color: '#726CFF'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '唠嗑处对象交朋友',
        room_num: '23',
        room_tag: '陪玩',
        color: '#726CFF'
      },
    ],
    /* 电台 */
    radioList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '处CP，唱歌，开黑',
        room_num: '3',
        room_tag: '处CP',
        color: '#FE4697'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '王者荣耀开黑',
        room_num: '45',
        room_tag: '王者荣耀',
        color: '#FE4697'
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '刺激战场',
        room_num: '11',
        room_tag: '绝地求生',
        color: '#FE4697'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '迷雾荣耀局金框',
        room_num: '23',
        room_tag: '球球大作战',
        color: '#FE4697'
      },
    ],
    /* 关注 */
    focusList: [
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '处CP，唱歌，开黑',
        room_num: '3',
        room_tag: '处CP',
        color: '#FE4697'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '王者荣耀开黑',
        room_num: '45',
        room_tag: '王者荣耀',
        color: '#726CFF'
      },
      {
        homeowners: '/images/room_cover_1.jpg',
        room_name: '刺激战场',
        room_num: '11',
        room_tag: '绝地求生',
        color: '#FE4697'
      },
      {
        homeowners: '/images/room_cover_2.jpg',
        room_name: '迷雾荣耀局金框',
        room_num: '23',
        room_tag: '球球大作战',
        color: '#F79C52'
      },
    ],
    nofocus: false,
    icon_nofocus: '/images/icon_nofocus.png',

    /* 附近 */
    ico_room: '/images/ico_room.png',
    ico_male: '/images/ico_male.png',
    ico_female: '/images/ico_female.png',
    nearbyList: [
      {
        nickname: '孙悟空不要饭孙悟空不要饭孙悟空不要饭孙悟空不要饭',
        avatar: '/images/room_cover_1.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下这个家伙忙着连麦，一句话都没留下',
        room: 0,
        distance: '附近',
        male: 1,
        age: 21,
      },
      {
        nickname: '小龙女',
        avatar: '/images/room_cover_2.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 1,
        distance: '400m',
        male: 0,
        age: 22,
      },
      {
        nickname: '可爱的她',
        avatar: '/images/room_cover_1.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 1,
        distance: '1.4km',
        male: 1,
        age: 23,
      },
      {
        nickname: '约那个啥',
        avatar: '/images/room_cover_2.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 0,
        distance: '2.4km',
        male: 0,
        age: 24,
      },


    ],
    /*个人中心左侧栏*/
    hideMask: true,
    hideInfo: true,
    info_bg: '/images/info_bg.png',
    comeback_icon: '/images/left_arrow.png',
    info_edit: '/images/info_edit.png',
    userInfo: {
      nickname: '小胖子..浪烧饼🍅',
      avatarUrl: '/images/logo_hi.png',
      id: '55667788'
    },
    infoList: [
      {
        icon: '/images/info_room.png',
        text: '房间',
      },
      {
        icon: '/images/info_account.png',
        text: '我的帐户',
      },
      {
        icon: '/images/info_gift.png',
        text: '我的礼物',
      },
      {
        icon: '/images/info_focus.png',
        text: '我的关注',
      },
      {
        icon: '/images/info_rank.png',
        text: '排行榜',
      },


    ],
  },
  hotUpper: function (e) {
    console.log('上拉刷新')
  },
  hotLower: function (e) {
    console.log('下拉加载')
  },
  hotScroll: function (e) {
    // console.log(e)
  },

  /* 滚动选项卡点击 Swiper 跳转到对应显示页 */
  tabSelect: function (e) {
    let idx = e.currentTarget.dataset.idx
    this.setData({
      curIdx: idx,
      curItem: idx,
      toView: 'tabs_' + (idx - 1)
    })

  },
  /* Swiper 内容改变时，滚动选项卡跳转到对应位置 */
  tabSwiperChange: function (e) {
    let i = e.detail.current
    this.setData({
      curIdx: i,
      toView: 'tabs_' + (i - 1)
    })

  },

  /*用户授权*/
  getUserInfo: function (e) {

    let _this = this
    app.getUserInfo(e, function (res) {
      Utils.log(`data:${JSON.stringify(res)}`)
      if (res) {
        _this.setData({
          userInfo: res,
          hasUserInfo: true
        })
      }
    })


    // this.setData({
    //   avatarUrl: e.detail.userInfo.avatarUrl,
    // })
    
  },
  /*滑入用户信息*/
  sliderUserInfo: function (e) {
    this.setData({
      hideMask: false,
      hideInfo: false,
    })
  },
  /*滑出用户信息*/
  hideUserInfo: function (e) {
    this.setData({
      hideInfo: true,
    })
    setTimeout(() => {
      this.setData({
        hideMask: true,
      })
    }, 500)
  },
  navToInfo: function (e) {
    let index = e.currentTarget.dataset.index;
    switch (index) {
      case 0:
        wx.navigateTo({
          url: '/pages/index/index'
        })
        break;
      case 1:
        break;
      case 2:
        break;
      case 3:
        break;
      case 4:
        break;
    }
  },
  /* 路由事件 */
  navtoNewRoom: function () { },
  navtoNewHomeowners: function () { },
  navtoGameHomeowners: function () { },
  navtoGameRoom: function () { },



  preventD: function (e) {
    // 无效的事件，阻止冒泡
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
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },
  onLoad: function (options) {

    // 解决 swiper 自适应高度问题 200为顶部head和tabs高度
    this.setData({
      scrollheight: app.globalData.windowHeight - (app.globalData.windowWidth / 750 * 200)
    });

    /* 生成随机色
      let gameList = this.data.gameList
      gameList.forEach((item)=>{
        let color = randomColor();
        item.color = color
        this.setData({
          gameList: gameList
        });
      })

    */
  },


  /**
 * 页面相关事件处理函数--监听用户下拉动作
 */
  onPullDownRefresh: function () {
    wx.showNavigationBarLoading() //在标题栏中显示加载
    //模拟加载
    setTimeout(function () {
      // complete
      wx.hideNavigationBarLoading() //完成停止加载
      wx.stopPullDownRefresh() //停止下拉刷新
    }, 1500);
  },

  //下拉刷新
  onPullDownRefresh: function () {
    var _this = this
    wx.showNavigationBarLoading() //在标题栏中显示加载
    //模拟加载
    setTimeout(function () {
      // complete
      _this.onShow()
      wx.hideNavigationBarLoading() //完成停止加载
      wx.stopPullDownRefresh() //停止下拉刷新
    }, 1500);
  },

})


// 设置随机数函数 返回十六进制颜色值
function randomColor() {
  // 随机生成6个 0到15 之间的随机数转换为十六进制 和 # 号拼接成十六进制颜色值
  var strColor = "#"
  for (var i = 0; i < 6; i++) {
    strColor += Math.floor(Math.random() * 16).toString(16)
  }
  return strColor
}

