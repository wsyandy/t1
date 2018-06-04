const app = getApp()
const request = require('../../utils/wxRequest.js');
const Utils = require('../../utils/util.js');
Page({
  data: {
    page: 1,
    hasUserInfo: false,
    isIos: app.globalData.isIos, /*设备是否为IOS*/
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    logo: '/images/logo_hi.png',
    avatarUrl: '',
    topTabs: [],
    // topTabs: ['热门', '最新', '开黑', '唱歌', '交友', '电台', '关注', '附近'],
    toView: '',
    curIdx: 0, /* scroll_tabs 索引值 与 curItem 对应 */
    curItem: 0, /*swiper-item 索引值 与 curIdx 对应*/
    stars: '/images/tag_stars.png',
    scrollheight: '',/*设置可滚动 高度*/
    ico_people: '/images/ico_people.png',
    lock: '/images/ico_lock.png',
    nofocus: false,
    icon_nofocus: '/images/icon_nofocus.png',
    /* 附近 */
    ico_room: '/images/ico_room.png',
    ico_male: '/images/ico_male.png',
    ico_female: '/images/ico_female.png',
    // nearbyList: [
    //   {
    //     nickname: '孙悟空不要饭孙悟空不要饭孙悟空不要饭孙悟空不要饭',
    //     avatar: '/images/room_cover_1.jpg',
    //     signature: '这个家伙忙着连麦，一句话都没留下这个家伙忙着连麦，一句话都没留下',
    //     room: 0,
    //     distance: '附近',
    //     male: 1,
    //     age: 21,
    //   },
    //   {
    //     nickname: '小龙女',
    //     avatar: '/images/room_cover_2.jpg',
    //     signature: '这个家伙忙着连麦，一句话都没留下',
    //     room: 1,
    //     distance: '400m',
    //     male: 0,
    //     age: 22,
    //   },
    //   {
    //     nickname: '可爱的她',
    //     avatar: '/images/room_cover_1.jpg',
    //     signature: '这个家伙忙着连麦，一句话都没留下',
    //     room: 1,
    //     distance: '1.4km',
    //     male: 1,
    //     age: 23,
    //   },
    //   {
    //     nickname: '约那个啥',
    //     avatar: '/images/room_cover_2.jpg',
    //     signature: '这个家伙忙着连麦，一句话都没留下',
    //     room: 0,
    //     distance: '2.4km',
    //     male: 0,
    //     age: 24,
    //   },

    /*个人中心左侧栏*/
    hideMask: true,
    hideInfo: true,
    info_bg: '/images/info_bg.png',
    comeback_icon: '/images/left_arrow.png',
    info_edit: '/images/info_edit.png',
    userInfo: {},
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
    /**
     * 首页分页加载所需数据
     */
    hotPage: 1,
    hotTotalPage: 1,
    hot: [],
    newPage: 1,
    newTotalPage: 1,
    new: [],
    gang_upPage: 1,
    gang_upTotalPage: 1,
    gang_up: [],
    friendPage: 1,
    friendTotalPage: 1,
    friend: [],
    amusePage: 1,
    amuseTotalPage: 1,
    amuse: [],
    singPage: 1,
    singTotalPage: 1,
    sing: [],
    broadcastPage: 1,
    broadcastTotalPage: 1,
    broadcast: [],
    followPage: 1,
    followTotalPage: 1,
    follow: [],
  },

  hotScroll: function (e) {
    // console.log(e)
  },

  /*用户授权*/
  getUserInfo: function (e) {

    app.getUserInfo(e, (res) => {
      Utils.log(`data:${JSON.stringify(res)}`)
      if (res) {
        this.setData({
          userInfo: res,
          hasUserInfo: true
        })
      }
    })

    // this.setData({
    //   avatarUrl: e.detail.userInfo.avatarUrl,
    //   hasUserInfo: true
    // })

    this.setData({
      avatarUrl: e.detail.userInfo.avatarUrl,
      hasUserInfo: true
    })

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

        var url = getCurrentPages()[getCurrentPages().length - 1].route //获取当前页面的路径
        Utils.log(`当前页面的路径:${url}`)
        if (url == 'pages/index/index') {
          this.setData({
            hideInfo: true,
          })
          setTimeout(() => {
            this.setData({
              hideMask: true,
            })
          }, 500)
        } else {
          wx.redirectTo({
            url: '/pages/index/index'
          })
        }
        break;
      case 1:
        wx.navigateTo({
          url: '/pages/my_account/my_account'
        })
        break;
      case 2:
        wx.navigateTo({
          url: '/pages/my_gift/my_gift'
        })
        break;
      case 3:
        wx.navigateTo({
          url: '/pages/my_focus/my_focus'
        })
        break;
      case 4:
        wx.navigateTo({
          url: '/pages/ranking/ranking'
        })
        break;
    }
  },
  /* 路由事件 */
  navToMyProfile: function () {
    wx.navigateTo({
      url: '/pages/my_profile/my_profile'
    })
  },
  navtoNewRoom: function () {
    console.log('最新')
  },
  navtoNewHomeowners: function () {
    console.log('点击最新分类下的房主头像')
  },
  // navtoGameHomeowners: function () { },
  navtoGameRoom: function () {
    console.log('开黑')
  },
  navtoFriendRoom: function () {
    console.log('交友')
  },
  navtoAmuseRoom: function () {
    console.log('娱乐')
  },
  navtoSingRoom: function () {
    console.log('唱歌')
  },
  navtoBroadcastingRoom: function () {
    console.log('电台')
  },
  navtoFollowRoom: function () {
    console.log('关注')
  },


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
    if (wx.getStorageSync('userInfo')) {
      this.setData({
        userInfo: wx.getStorageSync('userInfo'),
        hasUserInfo: true
      })
    } else if (!this.data.canIUse) {
      wx.showModal({
        title: "微信版本太旧",
        content: "使用旧版本微信，将无法登陆、使用一些功能。请至 App Store、Play Store 或其他可信渠道更新微信。",
        showCancel: false,
        confirmText: "好"
      })
    }
    this.showTab()//加载房间分类标题

  },
  /* 滚动选项卡点击 Swiper 跳转到对应显示页 */
  tabSelect: function (e) {
    let idx = e.currentTarget.dataset.idx
    console.log(idx)
    this.setData({
      curIdx: idx,
      curItem: idx,
      toView: 'tabs_' + (idx - 1)
    })

  },
  /* Swiper 内容改变时，滚动选项卡跳转到对应位置 */
  tabSwiperChange: function (e) {
    let i = e.detail.current
    console.log(i)
    this.setData({
      curIdx: i,
      toView: 'tabs_' + (i - 1)
    })

    // 从缓存中去当前分类下的数据，取到则不调用接口
    if (wx.getStorageSync(this.data.topTabs[i].type)) {
      this.setData({
        [this.data.topTabs[i].type]: wx.getStorageSync(this.data.topTabs[i].type),
        // roomList: wx.getStorageSync(this.data.topTabs[i].type),
        // roomList: {},//后续修改至统一参数，根据type进行判断展示什么样式
        // type: type,
      })
      return;
    }
    this.roomList(this.data.topTabs[i].type, this.data.topTabs[i].value)
  },

  showTab: function () {
    var _this = this
    request.postRequest('rooms/types').then(res => {
      if (res.data.error_code != 0) {
        wx.showToast({
          title: '请求失败',
          icon: "none",
          duration: 1000
        })
      } else {
        let types = res.data.types
        _this.setData({
          topTabs: types
        })
        // console.log(types)
        _this.data.curIdx = 0
        _this.roomList(types[0].type, types[0].value)
      }
    })
  },
  roomList: function (type, value) {
    // type = 'broadcast'//临时获取数据用
    if (this.ifPage()) {
      wx.showToast({
        title: '已经到底了',
        icon: 'none',
        duration: 1500
      })
      return;
    }

    let data = { [type]: value, page: this.data.page, per_page: 20 }
    let _this = this
    console.log(data)
    request.postRequest('rooms/index', data).then(res => {
      console.log(res.data.rooms)
      var roomList = res.data.rooms
      if (type == 'follow' && roomList.length >= 1) {
        _this.setData({
          nofocus: true
        })
      } else if (type == 'follow') {
        _this.setData({
          nofocus: false
        })
      }

      let roomData = _this.updatePage(res.data.total_page).concat(roomList)
      //当前页面的数据存入缓存，以分类名命名
      wx.setStorageSync(type, roomData)

      // if(data.hot){
      _this.setData({
        [type]: roomData,
        // roomList: roomList,
        // type: type
      })
      // }
    })

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
      // complete
      _this.roomList(_this.data.topTabs[_this.data.curIdx].type, _this.data.topTabs[_this.data.curIdx].value)
      wx.hideNavigationBarLoading() //完成停止加载
      wx.stopPullDownRefresh() //停止下拉刷新
    }, 1800);
  },
  /**
 * 页面上拉触底事件的处理函数
 */
  bindscrolltolower: function () {
    console.log("触底事件")
    this.roomList(this.data.topTabs[this.data.curIdx].type, this.data.topTabs[this.data.curIdx].value)
  },
  /**
   * 进入房间操作
   */
  enterRoom: function () {
    wx.navigateTo({
      url: '../room/room'
    })
  },
  /**
   * 更新totalPage,以及page
   */
  updatePage: function (totalPage) {
    var data = []
    switch (this.data.curIdx) {
      case 0:
        this.data.hotPage++
        this.data.hotTotalPage = totalPage
        data = this.data.hot
        break;
      case 1:
        this.data.newPage++
        this.data.newTotalPage = totalPage
        data = this.data.new
        break;
      case 2:
        this.data.gang_upPage++
        this.data.gang_upTotalPage = totalPage
        data = this.data.gang_up
        break;
      case 3:
        this.data.friendPage++
        this.data.friendTotalPage = totalPage
        data = this.data.friend
        break;
      case 4:
        this.data.amusePage++
        this.data.amuseTotalPage = totalPage
        data = this.data.amuse
        break;
      case 5:
        this.data.singPage++
        this.data.singTotalPage = totalPage
        data = this.data.sing
        break;
      case 6:
        this.data.broadcastPage++
        this.data.broadcastTotalPage = totalPage
        data = this.data.broadcast
        break;
      case 7:
        this.data.followPage++
        this.data.followTotalPage = totalPage
        data = this.data.follow
        break;
      case 8:
        break;
      default:
        break;
    }

    return data;
  },
  /**
   * 分页判断是否是最后一页
   */
  ifPage: function () {
    var ifPage = false
    switch (this.data.curIdx) {
      case 0:
        if (this.data.hotPage > this.data.hotTotalPage) {
          ifPage = true
        }
        break;
      case 1:
        if (this.data.newPage > this.data.newTotalPage) {
          ifPage = true
        }
        break;
      case 2:
        if (this.data.gang_upPage > this.data.gang_upTotalPage) {
          ifPage = true
        }
        break;
      case 3:
        if (this.data.friendPage > this.data.friendTotalPage) {
          ifPage = true
        }
        break;
      case 4:
        if (this.data.amusePage > this.data.amuseTotalPage) {
          ifPage = true
        }
        break;
      case 5:
        if (this.data.singPage > this.data.singTotalPage) {
          ifPage = true
        }
        break;
      case 6:
        if (this.data.broadcastPage > this.data.broadcastTotalPage) {
          ifPage = true
        }
        break;
      case 7:
        if (this.data.followPage > this.data.followTotalPage) {
          ifPage = true
        }
        break;
      case 8:
        break;
      default:
        break;
    }

    return ifPage;
  },
  /**
 * 初始化数据
 */
  initData: function () {
    switch (this.data.curIdx) {
      case 0:
        this.data.hotPage = 1
        this.data.hotTotalPage = 1
        this.data.hot = []
        break;
      case 1:
        this.data.newPage = 1
        this.data.newTotalPage = 1
        this.data.new = []
        break;
      case 2:
        this.data.gang_upPage = 1
        this.data.gang_upTotalPage = 1
        this.data.gang_up = []
        break;
      case 3:
        this.data.friendPage = 1
        this.data.friendTotalPage = 1
        this.data.friend = []
        break;
      case 4:
        this.data.amusePage = 1
        this.data.amuseTotalPage = 1
        this.data.amuse = []
        break;
      case 5:
        this.data.singPage = 1
        this.data.singTotalPage = 1
        this.data.sing = []
        break;
      case 6:
        this.data.broadcastPage = 1
        this.data.broadcastTotalPage = 1
        this.data.broadcast = []
        break;
      case 7:
        this.data.followPage = 1
        this.data.followTotalPage = 1
        this.data.follow = []
        break;
      case 8:
        break;
      default:
        break;
    }
    Utils.log(`初始化送出列表${this.data.curIdx}`)
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