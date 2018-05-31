// pages/room/room.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: { 
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    roomBg: '/images/room_bg1.png',
    ico_cup: '/images/ico_cup.png',
    arrowRight: '/images/arrow_right.png',
    icoSpeaker: '/images/ico_speaker.png',
    userInfo: app.globalData.userInfo,
    roomInfo:{
      room_name:'休闲听音乐',
      room_number: 10,
      homeowners: '/images/room_cover_1.jpg',
      nickname: '女神~经',
      signature: '我应在江湖悠悠饮一壶浊酒',
    },
    /*座位列表*/
    icoSofa: '/images/ico_sofa.png',
    icoProhibit: '/images/ico_prohibit.png',
    seat0: {
      avatar: '',       /*在座人员头像*/
      nickname: '',     /*在座人员昵称*/
      vacancy: true,    /*座位是否空位*/
      prohibit: false,  /*座位是否禁麦*/
      seal: false,      /*座位是否被封*/
      phonate: false    /*在座人员是否发言*/
    },
    seat1: {},
    seat2: {},
    seat3: {},
    seat4: {},
    seat5: {},
    seat6: {},
    seat7: {},
    seatList:[
      {
        avatar: '',       /*在座人员头像*/
        nickname: '',     /*在座人员昵称*/
        vacancy: true,    /*座位是否空位*/
        prohibit: false,  /*座位是否禁麦*/
        seal: false,      /*座位是否被封*/
        phonate: false    /*在座人员是否发言*/
      },
      {
        avatar: '',
        nickname: '',
        vacancy: true,
        prohibit: false,
        seal: false,
        phonate: false
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname:'女神1号',
        vacancy: false,
        prohibit: false,
        seal:false,
        phonate: false
      },
      {
        avatar: '/images/room_cover_2.jpg',
        nickname: '女神12号',
        vacancy: false,
        prohibit: false,
        seal: false,
        phonate: true
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname: '女神12号',
        vacancy: false,
        prohibit: false,
        seal: false,
        phonate: true
      },
      {
        avatar: '',
        nickname: '',
        vacancy: false,
        prohibit: false,
        seal: true,
        phonate: false
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname: '女神1号',
        vacancy: false,
        prohibit: true,
        seal: false,
        phonate: false
      },
      {
        avatar: '',
        nickname: '',
        vacancy: false,
        prohibit: false,
        seal: true,
        phonate: false
      },
    ],
    mute: false,
    prohibit: false,
    tabbar:[
      {
        icon: '/images/bar_speaker.png',
        mute: '/images/bar_speaker_mute.png',
      },
      {
        icon: '/images/bar_microphone_prohibit.png',
        prohibit: '/images/bar_microphone.png',
      },
      {
        icon: '/images/bar_gift.png', 
      },
      {
        icon: '/images/bar_write.png',
      },
      {
        icon: '/images/bar_share.png',
      }
    ],
    bulletinHeight: 0,/*设置可滚动 高度*/
    bulletinList: [
      { 
        nickname: '阿狸',
        system: '进入房间',
      },
      {
        nickname: '阿狸', 
        info: '小姐姐你好',
      },
      {
        nickname: '阿狸',
        info: '小姐姐你好',
      },
      {
        nickname: '阿狸',
        info: '小姐姐你好',
      },
      {
        nickname: '阿狸',
        info: '小姐姐你好',
      },
    ],
    hideMask: true,
    hideAuth: true,
    hideLogin: true,
    authClose: '/images/auth_close.png',
    telVal:'', 
  },
  /*用户授权*/
  bindGetUserInfo: function (e) {
    // console.log(e.detail.userInfo)
    this.setData({
      hideAuth: true,
      hideMask: true,
      userInfo: e.detail.userInfo,
    })
  },
  //显示登录弹窗
  closeAuth:function(){
    this.setData({
      hideAuth:true,
      hideMask: true,
    })
  },
    //关闭授权弹窗
  showLogin: function () {
    this.setData({
      hideAuth: true, 
      hideLogin: false,
    })
  },
  //关闭登录弹窗
  closeLogin: function () {
    this.setData({
      hideMask: true,
      hideLogin: true,
    })
  },

  beSeated:function(e){
    let i = e.currentTarget.dataset.index, 
        userInfo = this.data.userInfo,
        seatList = this.data.seatList, 
        vacancy = seatList[i].vacancy,   /*座位是否空位*/
        prohibit = seatList[i].prohibit, /*座位是否禁麦*/
        seal = seatList[i].seal;         /*座位是否被封*/
 
    console.log(userInfo)
        if (seal) return; /*座位被封 无操作*/
    if (vacancy){ /*当前空位，自己上麦*/
      /*判断是否登录*/
      if (userInfo){ // 已经登录
       
        seatList[i].avatar = userInfo.avatarUrl;
        seatList[i].nickname = userInfo.nickName;
        seatList[i].vacancy = false;
        this.setData({
          seatList: seatList, 
        })

      } else { // 未登录 弹窗授权弹窗
        this.setData({
          hideAuth: false,
          hideMask: false,
        })

      }
    }
    console.log(this.data.seatList)
    
  },

  tabbarNav:function(e){
    let index = e.currentTarget.dataset.index;
    let tabbar = this.data.tabbar
    // console.log(index)
    switch (index){
      case 0: 
        this.setData({
          mute: !this.data.mute, 
        })
        
        break;
      case 1: 
        this.setData({
          prohibit: !this.data.prohibit,
        }) 
        break;
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 解决 公告栏 自适应高度问题 880为顶部head+seat+tabbar高度
    this.setData({
      bulletinHeight: app.globalData.windowHeight - (app.globalData.windowWidth / 750 * 880)
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

