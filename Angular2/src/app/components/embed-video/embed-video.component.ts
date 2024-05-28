import { Component, OnInit, Input } from '@angular/core';
import {DomSanitizer} from '@angular/platform-browser';

@Component({
  selector: 'embed-video',
  templateUrl: './embed-video.component.html',
  styleUrls: ['./embed-video.component.less']
})
export class EmbedVideoComponent implements OnInit {
  @Input() height: any;
  @Input() width: any;
  @Input() attrs: any;
  @Input() href: string;
  @Input() itemId: number;

  public id;
  public trustedVideoSrc;
  constructor(private sanitizer: DomSanitizer) {}

  ngOnInit() {
    var href = void 0;

    if (void 0 !== this.href && this.href !== href) {
      href = this.href;
      var currentPlayer = null;

      for(let players of this.RegisteredPlayers()){
        players.isPlayerFromURL(this.href) && (currentPlayer = players)
      }

      // if (null === currentPlayer)
        // return void scope.onChange();
      {
        var j = this.href.match(currentPlayer.playerRegExp),
            k = j[2],
            l = j[1],
            m = this.href.match(currentPlayer.timeRegExp);
        currentPlayer.config
      };

      for(let b in this.whitelist(this.attrs, currentPlayer.whitelist)){
        var c = void 0 != currentPlayer.transformAttrMap[b] ? currentPlayer.transformAttrMap[b] : b;
        currentPlayer.settings[c] = this.whitelist(this.attrs, currentPlayer.whitelist)[b];
      }
      currentPlayer.settings.start = 0;
      if (m)
        switch (currentPlayer.type) {
          case "youtube":
            currentPlayer.settings.start += 60 * parseInt(m[2] || "0") * 60,
                currentPlayer.settings.start += 60 * parseInt(m[4] || "0"),
                currentPlayer.settings.start += parseInt(m[6] || "0");
            break;
          case "dailymotion":
            currentPlayer.settings.start += parseInt(m[1] || "0")
        }
      // if (i.isAdditionaResRequired())
        // for (var n = angular.element(d.document.querySelector("body")), o = 0; o < i.additionalRes.length; o++) {
        //   var p = i.additionalRes[o];
        //   null == d.document.querySelector("#" + p.id) && n.append(p.element)
        // }

      // scope.onChange({
      //   videoId: k,
      //   provider: i.type
      // });
      let url = currentPlayer.buildSrcURL(l, k);
      this.trustedVideoSrc = this.sanitizer.bypassSecurityTrustResourceUrl(url);
    }
  }

  PlayerConfig(){
    let createInstance = function(a) {
      var b = function(a) {
            this.type = a.type,
            this.obj = a.obj,
                this.playerRegExp = a.playerRegExp,
                this.timeRegExp = a.timeRegExp,
                this.whitelist = a.whitelist,
                this.playerID = a.playerID,
                this.settings = a.settings,
                this.transformAttrMap = a.transformAttrMap,
                this.processSettings = a.processSettings,
                this.isPlayerFromURL = function(a) {
                  return null != a.match(this.playerRegExp)
                }
                ,
                this.buildSrcURL = a.buildSrcURL,
                this.isAdditionaResRequired = a.isAdditionaResRequired,
                this.additionalRes = a.additionalRes
          }
          ;
      return new b(a)
    };

    return createInstance;
  }

  RegisteredPlayers(){
    let obj = this;
    let video_types = {
      youtube: {
        type: "youtube",
        obj: obj,
        settings: {
          autoplay: 0,
          controls: 1,
          loop: 0
        },
        whitelist: ["autohide", "cc_load_policy", "color", "disablekb", "enablejsapi", "autoplay", "controls", "loop", "playlist", "rel", "wmode", "start", "showinfo", "end", "fs", "hl", "iv_load_policy", "list", "listType", "modestbranding", "origin", "playerapiid", "playsinline", "theme"],
        transformAttrMap: {},
        processSettings: function(a, b) {
          return 1 == a.loop && void 0 == a.playlist && (a.playlist = b),a
        },
        buildSrcURL: function(a, c) {
          return a + this.playerID + c + this.obj.videoSettings(this.processSettings(this.settings))
        },
        playerID: "www.youtube.com/embed/",
        playerRegExp: /([a-z\:\/]*\/\/)(?:www\.)?(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
        timeRegExp: /t=(([0-9]+)h)?(([0-9]{1,2})m)?(([0-9]+)s?)?/,
        isAdditionaResRequired: function() {
          return !1
        },
        additionalRes: []
      },
      vimeo: {
        type: "vimeo",
        obj: obj,
        settings: {
          autoplay: 0,
          loop: 0,
          api: 0,
          player_id: ""
        },
        whitelist: ["autoplay", "autopause", "badge", "byline", "color", "portrait", "loop", "api", "playerId", "title"],
        transformAttrMap: {
          playerId: "player_id"
        },
        processSettings: function(a) {
          return a
        },
        buildSrcURL: function(a, c) {
          return a + this.playerID + c + this.obj.videoSettings(this.processSettings(this.settings))
        },
        playerID: "player.vimeo.com/video/",
        playerRegExp: /([a-z\:\/]*\/\/)(?:www\.)?vimeo\.com\/(?:channels\/[A-Za-z0-9]+\/)?([A-Za-z0-9]+)/,
        timeRegExp: "",
        isAdditionaResRequired: function() {
          return !1
        },
        additionalRes: []
      },
      dailymotion: {
        type: "dailymotion",
        obj: obj,
        settings: {
          autoPlay: 0,
          logo: 0
        },
        whitelist: ["api", "autoPlay", "background", "chromeless", "controls", "foreground", "highlight", "html", "id", "info", "logo", "network", "quality", "related", "startscreen", "webkit-playsinline", "syndication"],
        transformAttrMap: {},
        processSettings: function(a) {
          return a
        },
        buildSrcURL: function(a, c) {
          return a + this.playerID + c + this.obj.videoSettings(this.processSettings(this.settings))
        },
        playerID: "www.dailymotion.com/embed/video/",
        playerRegExp: /([a-z\:\/]*\/\/)(?:www\.)?www\.dailymotion\.com\/video\/([A-Za-z0-9]+)/,
        timeRegExp: /start=([0-9]+)/,
        isAdditionaResRequired: function() {
          return !1
        },
        additionalRes: []
      },
      youku: {
        type: "youku",
        obj: obj,
        settings: {},
        whitelist: [],
        transformAttrMap: {},
        processSettings: function(a) {
          return a
        },
        buildSrcURL: function(a, c) {
          return a + this.playerID + c + this.obj.videoSettings(this.processSettings(this.settings))
        },
        playerID: "player.youku.com/embed/",
        playerRegExp: /([a-z\:\/]*\/\/)(?:www\.)?youku\.com\/v_show\/id_([A-Za-z0-9]+).html/,
        timeRegExp: "",
        isAdditionaResRequired: function() {
          return !1
        },
        additionalRes: []
      },
      vine: {
        type: "vine",
        obj: obj,
        settings: {
          audio: 0,
          start: 0,
          type: "simple"
        },
        whitelist: ["audio", "start", "type"],
        transformAttrMap: {},
        processSettings: function(a) {
          return a
        },
        buildSrcURL: function(a, c) {
          var d = this.settings.type;
          return a + this.playerID + c + /embed/ + d + this.obj.videoSettings(this.processSettings(this.settings));
        },
        playerID: "vine.co/v/",
        playerRegExp: /([a-z\:\/]*\/\/)(?:www\.)?vine\.co\/v\/([A-Za-z0-9]+)/,
        timeRegExp: "",
        isAdditionaResRequired: function() {
          // return !$window.VINE_EMBEDS
        },
        additionalRes: [{
          id: "ng-video-embed-vine-res-1",
          element: '<script id="ng-video-embed-vine-res-1" src="//platform.vine.co/static/scripts/embed.js"></script>'
        }]
      }
    },
    video_type_objects = [];

    for(let index in video_types){
      video_type_objects.push(this.PlayerConfig()(video_types[index]))
    }

    return video_type_objects;
  }

  whitelist(a, b) {
      var c = {};
      for(let d in a){
        -1 != b.indexOf(d) && (c[d] = a[d])
      }
      return c;
  }

  videoSettings(a){
      var b = [];
      for(let c in a){
        b.push([c, a[c]].join("="))
      }
      return b.length > 0 ? "?" + b.join("&") : "";
  }
}