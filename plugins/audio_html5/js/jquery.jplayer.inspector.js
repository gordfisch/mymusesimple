/*
 * jPlayerInspector Plugin for jPlayer Plugin for jQuery JavaScript Library
 * http://www.jplayer.org
 *
 * Copyright (c) 2009 - 2013 Happyworm Ltd
 * Dual licensed under the MIT and GPL licenses.
 *  - http://www.opensource.org/licenses/mit-license.php
 *  - http://www.gnu.org/copyleft/gpl.html
 *
 * Author: Mark J Panaghiston
 * Version: 1.0.4
 * Date: 29th January 2013
 *
 * For use with jPlayer Version: 2.2.19+
 *
 * Note: Declare inspector instances after jPlayer instances. ie., Otherwise the jPlayer instance is nonsense.
 */

(function($, undefined) {
	$.jPlayerInspector = {};
	$.jPlayerInspector.i = 0;
	$.jPlayerInspector.defaults = {
		jPlayer: undefined, // The jQuery selector of the jPlayer instance to inspect.
		idPrefix: "jplayer_inspector_",
		visible: false
	};
	
	var methods = {
		init: function(options) {
			var self = this;
			var $this = $(this);
			
			var config = $.extend({}, $.jPlayerInspector.defaults, options);
			$(this).data("jPlayerInspector", config);

			config.id = $(this).attr("id");
			config.jPlayerId = config.jPlayer.attr("id");

			config.windowId = config.idPrefix + "window_" + $.jPlayerInspector.i;
			config.statusId = config.idPrefix + "status_" + $.jPlayerInspector.i;
			config.configId = config.idPrefix + "config_" + $.jPlayerInspector.i;
			config.toggleId = config.idPrefix + "toggle_" + $.jPlayerInspector.i;
			config.eventResetId = config.idPrefix + "event_reset_" + $.jPlayerInspector.i;
			config.updateId = config.idPrefix + "update_" + $.jPlayerInspector.i;
			config.eventWindowId = config.idPrefix + "event_window_" + $.jPlayerInspector.i;
			
			config.eventId = {};
			config.eventJq = {};
			config.eventTimeout = {};
			config.eventOccurrence = {};
			
			$.each($.jPlayer.event, function(eventName,eventType) {
				config.eventId[eventType] = config.idPrefix + "event_" + eventName + "_" + $.jPlayerInspector.i;
				config.eventOccurrence[eventType] = 0;
			});
			
			var structure = 
				'<p><a href="#" id="' + config.toggleId + '">' + (config.visible ? "Hide" : "Show") + '</a> jPlayer Inspector</p>' 
				+ '<div id="' + config.windowId + '">'
					+ '<div id="' + config.statusId + '"></div>'
					+ '<div id="' + config.eventWindowId + '" style="padding:5px 5px 0 5px;background-color:#eee;border:1px dotted #000;">'
						+ '<p style="margin:0 0 10px 0;"><strong>jPlayer events that have occurred over the past 1 second:</strong>'
						+ '<br />(Backgrounds: <span style="padding:0 5px;background-color:#eee;border:1px dotted #000;">Never occurred</span> <span style="padding:0 5px;background-color:#fff;border:1px dotted #000;">Occurred before</span> <span style="padding:0 5px;background-color:#9f9;border:1px dotted #000;">Occurred</span> <span style="padding:0 5px;background-color:#ff9;border:1px dotted #000;">Multiple occurrences</span> <a href="#" id="' + config.eventResetId + '">reset</a>)</p>';
			
			// MJP: Would use the next 3 lines for ease, but the events are just slapped on the page.
			// $.each($.jPlayer.event, function(eventName,eventType) {
			// 	structure += '<div id="' + config.eventId[eventType] + '" style="float:left;">' + eventName + '</div>';
			// });
			
			var eventStyle = "float:left;margin:0 5px 5px 0;padding:0 5px;border:1px dotted #000;";
			// MJP: Doing it longhand so order and layout easier to control.
			structure +=
						'<div id="' + config.eventId[$.jPlayer.event.ready] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.flashreset] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.resize] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.repeat] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.click] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.error] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.warning] + '" style="' + eventStyle + '"></div>'

						+ '<div id="' + config.eventId[$.jPlayer.event.loadstart] + '" style="clear:left;' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.progress] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.timeupdate] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.volumechange] + '" style="' + eventStyle + '"></div>'

						+ '<div id="' + config.eventId[$.jPlayer.event.play] + '" style="clear:left;' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.pause] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.waiting] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.playing] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.seeking] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.seeked] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.ended] + '" style="' + eventStyle + '"></div>'

						+ '<div id="' + config.eventId[$.jPlayer.event.loadeddata] + '" style="clear:left;' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.loadedmetadata] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.canplay] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.canplaythrough] + '" style="' + eventStyle + '"></div>'

						+ '<div id="' + config.eventId[$.jPlayer.event.suspend] + '" style="clear:left;' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.abort] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.emptied] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.stalled] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.ratechange] + '" style="' + eventStyle + '"></div>'
						+ '<div id="' + config.eventId[$.jPlayer.event.durationchange] + '" style="' + eventStyle + '"></div>'

						+ '<div style="clear:both"></div>';

			// MJP: Would like a check here in case we missed an event.

			// MJP: Check fails, since it is not on the page yet.
/*			$.each($.jPlayer.event, function(eventName,eventType) {
				if($("#" + config.eventId[eventType])[0] === undefined) {
					structure += '<div id="' + config.eventId[eventType] + '" style="clear:left;' + eventStyle + '">' + eventName + '</div>';
				}
			});
*/
			structure +=
					'</div>'
					+ '<p><a href="#" id="' + config.updateId + '">Update</a> jPlayer Inspector</p>'
					+ '<div id="' + config.configId + '"></div>'
				+ '</div>';
			$(this).html(structure);
			
			config.windowJq = $("#" + config.windowId);
			config.statusJq = $("#" + config.statusId);
			config.configJq = $("#" + config.configId);
			config.toggleJq = $("#" + config.toggleId);
			config.eventResetJq = $("#" + config.eventResetId);
			config.updateJq = $("#" + config.updateId);

			$.each($.jPlayer.event, function(eventName,eventType) {
				config.eventJq[eventType] = $("#" + config.eventId[eventType]);
				config.eventJq[eventType].text(eventName + " (" + config.eventOccurrence[eventType] + ")"); // Sets the text to the event name and (0);
				
				config.jPlayer.bind(eventType + ".jPlayerInspector", function(e) {
					config.eventOccurrence[e.type]++;
					if(config.eventOccurrence[e.type] > 1) {
						config.eventJq[e.type].css("background-color","#ff9");
					} else {
						config.eventJq[e.type].css("background-color","#9f9");
					}
					config.eventJq[e.type].text(eventName + " (" + config.eventOccurrence[e.type] + ")");
					// The timer to handle the color
					clearTimeout(config.eventTimeout[e.type]);
					config.eventTimeout[e.type] = setTimeout(function() {
						config.eventJq[e.type].css("background-color","#fff");
					}, 1000);
					// The timer to handle the occurences.
					setTimeout(function() {
						config.eventOccurrence[e.type]--;
						config.eventJq[e.type].text(eventName + " (" + config.eventOccurrence[e.type] + ")");
					}, 1000);
					if(config.visible) { // Update the status, if inspector open.
						$this.jPlayerInspector("updateStatus");
					}
				});
			});

			config.jPlayer.bind($.jPlayer.event.ready + ".jPlayerInspector", function(e) {
				$this.jPlayerInspector("updateConfig");
			});

			config.toggleJq.click(function() {
				if(config.visible) {
					$(this).text("Show");
					config.windowJq.hide();
					config.statusJq.empty();
					config.configJq.empty();
				} else {
					$(this).text("Hide");
					config.windowJq.show();
					config.updateJq.click();
				}
				config.visible = !config.visible;
				$(this).blur();
				return false;
			});

			config.eventResetJq.click(function() {
				$.each($.jPlayer.event, function(eventName,eventType) {
					config.eventJq[eventType].css("background-color","#eee");
				});
				$(this * Playlist Object for the jPlayer Plugin
						(function(b,f){jPlayerPlaylist=function(a,c,d){var e=this;this.current=0;this.removing=this.shuffled=this.loop=!1;this.cssSelector=b.extend({},this._cssSelector,a);this.options=b.extend(!0,{keyBindings:{next:{key:39,fn:function(){e.next()}},previous:{key:37,fn:function(){e.previous()}}}},this._options,d);this.playlist=[];this.original=[];this._initPlaylist(c);this.cssSelector.title=this.cssSelector.cssSelectorAncestor+" .jp-title";this.cssSelector.playlist=this.cssSelector.cssSelectorAncestor+" .jp-playlist";
						this.cssSelector.next=this.cssSelector.cssSelectorAncestor+" .jp-next";this.cssSelector.previous=this.cssSelector.cssSelectorAncestor+" .jp-previous";this.cssSelector.shuffle=this.cssSelector.cssSelectorAncestor+" .jp-shuffle";this.cssSelector.shuffleOff=this.cssSelector.cssSelectorAncestor+" .jp-shuffle-off";this.options.cssSelectorAncestor=this.cssSelector.cssSelectorAncestor;this.options.repeat=function(a){e.loop=a.jPlayer.options.loop};b(this.cssSelector.jPlayer).bind(b.jPlayer.event.ready,function(){e._init()});
						b(this.cssSelector.jPlayer).bind(b.jPlayer.event.ended,function(){e.next()});b(this.cssSelector.jPlayer).bind(b.jPlayer.event.play,function(){b(this).jPlayer("pauseOthers")});b(this.cssSelector.jPlayer).bind(b.jPlayer.event.resize,function(a){a.jPlayer.options.fullScreen?b(e.cssSelector.title).show():b(e.cssSelector.title).hide()});b(this.cssSelector.previous).click(function(){e.previous();b(this).blur();return!1});b(this.cssSelector.next).click(function(){e.next();b(this).blur();return!1});b(this.cssSelector.shuffle).click(function(){e.shuffle(!0);
						return!1});b(this.cssSelector.shuffleOff).click(function(){e.shuffle(!1);return!1}).hide();this.options.fullScreen||b(this.cssSelector.title).hide();b(this.cssSelector.playlist+" ul").empty();this._createItemHandlers();b(this.cssSelector.jPlayer).jPlayer(this.options)};jPlayerPlaylist.prototype={_cssSelector:{jPlayer:"#jquery_jplayer_1",cssSelectorAncestor:"#jp_container_1"},_options:{playlistOptions:{autoPlay:!1,loopOnPrevious:!1,shuffleOnLoop:!0,enableRemoveControls:!1,displayTime:"slow",addTime:"fast",
						removeTime:"fast",shuffleTime:"slow",itemClass:"jp-playlist-item",freeGroupClass:"jp-free-media",freeItemClass:"jp-playlist-item-free",removeItemClass:"jp-playlist-item-remove"}},option:function(a,b){if(b===f)return this.options.playlistOptions[a];this.options.playlistOptions[a]=b;switch(a){case "enableRemoveControls":this._updateControls();break;case "itemClass":case "freeGroupClass":case "freeItemClass":case "removeItemClass":this._refresh(!0),this._createItemHandlers()}return this},_init:function(){var a=
						this;this._refresh(function(){a.options.playlistOptions.autoPlay?a.play(a.current):a.select(a.current)})},_initPlaylist:function(a){this.current=0;this.removing=this.shuffled=!1;this.original=b.extend(!0,[],a);this._originalPlaylist()},_originalPlaylist:function(){var a=this;this.playlist=[];b.each(this.original,function(b){a.playlist[b]=a.original[b]})},_refresh:function(a){var c=this;if(a&&!b.isFunction(a))b(this.cssSelector.playlist+" ul").empty(),b.each(this.playlist,function(a){b(c.cssSelector.playlist+
						" ul").append(c._createListItem(c.playlist[a]))}),this._updateControls();else{var d=b(this.cssSelector.playlist+" ul").children().length?this.options.playlistOptions.displayTime:0;b(this.cssSelector.playlist+" ul").slideUp(d,function(){var d=b(this);b(this).empty();b.each(c.playlist,function(a){d.append(c._createListItem(c.playlist[a]))});c._updateControls();b.isFunction(a)&&a();c.playlist.length?b(this).slideDown(c.options.playlistOptions.displayTime):b(this).show()})}},_createListItem:function(a){var c=
						this,d="<li><div>",d=d+("<a href='javascript:;' class='"+this.options.playlistOptions.removeItemClass+"'>&times;</a>");if(a.free){var e=!0,d=d+("<span class='"+this.options.playlistOptions.freeGroupClass+"'>(");b.each(a,function(a,f){b.jPlayer.prototype.format[a]&&(e?e=!1:d+=" | ",d+="<a class='"+c.options.playlistOptions.freeItemClass+"' href='"+f+"' tabindex='1'>"+a+"</a>")});d+=")</span>"}d+="<a href='javascript:;' class='"+this.options.playlistOptions.itemClass+"' tabindex='1'>"+a.title+(a.artist?
						" <span class='jp-artist'>by "+a.artist+"</span>":"")+"</a>";return d+="</div></li>"},_createItemHandlers:function(){var a=this;b(this.cssSelector.playlist).off("click","a."+this.options.playlistOptions.itemClass).on("click","a."+this.options.playlistOptions.itemClass,function(){var c=b(this).parent().parent().index();a.current!==c?a.play(c):b(a.cssSelector.jPlayer).jPlayer("play");b(this).blur();return!1});b(this.cssSelector.playlist).off("click","a."+this.options.playlistOptions.freeItemClass).on("click",
						"a."+this.options.playlistOptions.freeItemClass,function(){b(this).parent().parent().find("."+a.options.playlistOptions.itemClass).click();b(this).blur();return!1});b(this.cssSelector.playlist).off("click","a."+this.options.playlistOptions.removeItemClass).on("click","a."+this.options.playlistOptions.removeItemClass,function(){var c=b(this).parent().parent().index();a.remove(c);b(this).blur();return!1})},_updateControls:function(){this.options.playlistOptions.enableRemoveControls?b(this.cssSelector.playlist+
						" ."+this.options.playlistOptions.removeItemClass).show():b(this.cssSelector.playlist+" ."+this.options.playlistOptions.removeItemClass).hide();this.shuffled?(b(this.cssSelector.shuffleOff).show(),b(this.cssSelector.shuffle).hide()):(b(this.cssSelector.shuffleOff).hide(),b(this.cssSelector.shuffle).show())},_highlight:function(a){this.playlist.length&&a!==f&&(b(this.cssSelector.playlist+" .jp-playlist-current").removeClass("jp-playlist-current"),b(this.cssSelector.playlist+" li:nth-child("+(a+1)+
						")").addClass("jp-playlist-current").find(".jp-playlist-item").addClass("jp-playlist-current"),b(this.cssSelector.title+" li").html(this.playlist[a].title+(this.playlist[a].artist?" <span class='jp-artist'>by "+this.playlist[a].artist+"</span>":"")))},setPlaylist:function(a){this._initPlaylist(a);this._init()},add:function(a,c){b(this.cssSelector.playlist+" ul").append(this._createListItem(a)).find("li:last-child").hide().slideDown(this.options.playlistOptions.addTime);this._updateControls();this.original.push(a);
						this.playlist.push(a);c?this.play(this.playlist.length-1):1===this.original.length&&this.select(0)},remove:function(a){var c=this;if(a===f)return this._initPlaylist([]),this._refresh(function(){b(c.cssSelector.jPlayer).jPlayer("clearMedia")}),!0;if(this.removing)return!1;a=0>a?c.original.length+a:a;0<=a&&a<this.playlist.length&&(this.removing=!0,b(this.cssSelector.playlist+" li:nth-child("+(a+1)+")").slideUp(this.options.playlistOptions.removeTime,function(){b(this).remove();if(c.shuffled){var d=
						c.playlist[a];b.each(c.original,function(a){if(c.original[a]===d)return c.original.splice(a,1),!1})}else c.original.splice(a,1);c.playlist.splice(a,1);c.original.length?a===c.current?(c.current=a<c.original.length?c.current:c.original.length-1,c.select(c.current)):a<c.current&&c.current--:(b(c.cssSelector.jPlayer).jPlayer("clearMedia"),c.current=0,c.shuffled=!1,c._updateControls());c.removing=!1}));return!0},select:function(a){a=0>a?this.original.length+a:a;0<=a&&a<this.playlist.length?(this.current=
						a,this._highlight(a),b(this.cssSelector.jPlayer).jPlayer("setMedia",this.playlist[this.current])):this.current=0},play:function(a){a=0>a?this.original.length+a:a;0<=a&&a<this.playlist.length?this.playlist.length&&(this.select(a),b(this.cssSelector.jPlayer).jPlayer("play")):a===f&&b(this.cssSelector.jPlayer).jPlayer("play")},pause:function(){b(this.cssSelector.jPlayer).jPlayer("pause")},next:function(){var a=this.current+1<this.playlist.length?this.current+1:0;this.loop?0===a&&this.shuffled&&this.options.playlistOptions.shuffleOnLoop&&
						1<this.playlist.length?this.shuffle(!0,!0):this.play(a):0<a&&this.play(a)},previous:function(){var a=0<=this.current-1?this.current-1:this.playlist.length-1;(this.loop&&this.options.playlistOptions.loopOnPrevious||a<this.playlist.length-1)&&this.play(a)},shuffle:function(a,c){var d=this;a===f&&(a=!this.shuffled);(a||a!==this.shuffled)&&b(this.cssSelector.playlist+" ul").slideUp(this.options.playlistOptions.shuffleTime,function(){(d.shuffled=a)?d.playlist.sort(function(){return 0.5-Math.random()}):
						d._originalPlaylist();d._refresh(!0);c||!b(d.cssSelector.jPlayer).data("jPlayer").status.paused?d.play(0):d.select(0);b(this).slideDown(d.options.playlistOptions.shuffleTime)})}}})(jQuery);).blur();
				return false;
			});

			config.updateJq.click(function() {
				$this.jPlayerInspector("updateStatus");
				$this.jPlayerInspector("updateConfig");
				return false;
			});

			if(!config.visible) {
				config.windowJq.hide();
			} else {
				// config.updateJq.click();
			}
			
			$.jPlayerInspector.i++;

			return this;
		},
		destroy: function() {
			$(this).data("jPlayerInspector") && $(this).data("jPlayerInspector").jPlayer.unbind(".jPlayerInspector");
			$(this).empty();
		},
		updateConfig: function() { // This displays information about jPlayer's configuration in inspector
		
			var jPlayerInfo = "<p>This jPlayer instance is running in your browser where:<br />"

			for(i = 0; i < $(this).data("jPlayerInspector").jPlayer.data("jPlayer").solutions.length; i++) {
				var solution = $(this).data("jPlayerInspector").jPlayer.data("jPlayer").solutions[i];
				jPlayerInfo += "&nbsp;jPlayer's <strong>" + solution + "</strong> solution is";				
				if($(this).data("jPlayerInspector").jPlayer.data("jPlayer")[solution].used) {
					jPlayerInfo += " being <strong>used</strong> and will support:<strong>";
					for(format in $(this).data("jPlayerInspector").jPlayer.data("jPlayer")[solution].support) {
						if($(this).data("jPlayerInspector").jPlayer.data("jPlayer")[solution].support[format]) {
							jPlayerInfo += " " + format;
						}
					}
					jPlayerInfo += "</strong><br />";
				} else {
					jPlayerInfo += " <strong>not required</strong><br />";
				}
			}
			jPlayerInfo += "</p>";

			if($(this).data("jPlayerInspector").jPlayer.data("jPlayer").html.active) {
				if($(this).data("jPlayerInspector").jPlayer.data("jPlayer").flash.active) {
					jPlayerInfo += "<strong>Problem with jPlayer since both HTML5 and Flash are active.</strong>";
				} else {
					jPlayerInfo += "The <strong>HTML5 is active</strong>.";
				}
			} else {
				if($(this).data("jPlayerInspector").jPlayer.data("jPlayer").flash.active) {
					jPlayerInfo += "The <strong>Flash is active</strong>.";
				} else {
					jPlayerInfo += "No solution is currently active. jPlayer needs a setMedia().";
				}
			}
			jPlayerInfo += "</p>";

			var formatType = $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.formatType;
			jPlayerInfo += "<p><code>status.formatType = '" + formatType + "'</code><br />";
			if(formatType) {
				jPlayerInfo += "<code>Browser canPlay('" + $.jPlayer.prototype.format[formatType].codec + "')</code>";
			} else {
				jPlayerInfo += "</p>";
			}
			
			jPlayerInfo += "<p><code>status.src = '" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.src + "'</code></p>";

			jPlayerInfo += "<p><code>status.media = {<br />";
			for(prop in $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.media) {
				jPlayerInfo += "&nbsp;" + prop + ": " + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.media[prop] + "<br />"; // Some are strings
			}
			jPlayerInfo += "};</code></p>"

			jPlayerInfo += "<p>";
			jPlayerInfo += "<code>status.videoWidth = '" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.videoWidth + "'</code>";
			jPlayerInfo += " | <code>status.videoHeight = '" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.videoHeight + "'</code>";
			jPlayerInfo += "<br /><code>status.width = '" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.width + "'</code>";
			jPlayerInfo += " | <code>status.height = '" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.height + "'</code>";
			jPlayerInfo += "</p>";

			+ "<p>Raw browser test for HTML5 support. Should equal a function if HTML5 is available.<br />";
			if($(this).data("jPlayerInspector").jPlayer.data("jPlayer").html.audio.available) {
				jPlayerInfo += "<code>htmlElement.audio.canPlayType = " + (typeof $(this).data("jPlayerInspector").jPlayer.data("jPlayer").htmlElement.audio.canPlayType) +"</code><br />"
			}
			if($(this).data("jPlayerInspector").jPlayer.data("jPlayer").html.video.available) {
				jPlayerInfo += "<code>htmlElement.video.canPlayType = " + (typeof $(this).data("jPlayerInspector").jPlayer.data("jPlayer").htmlElement.video.canPlayType) +"</code>";
			}
			jPlayerInfo += "</p>";

			jPlayerInfo += "<p>This instance is using the constructor options:<br />"
			+ "<code>$('#" + $(this).data("jPlayerInspector").jPlayer.data("jPlayer").internal.self.id + "').jPlayer({<br />"

			+ "&nbsp;swfPath: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "swfPath") + "',<br />"

			+ "&nbsp;solution: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "solution") + "',<br />"

			+ "&nbsp;supplied: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "supplied") + "',<br />"

			+ "&nbsp;preload: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "preload") + "',<br />"
			
			+ "&nbsp;volume: " + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "volume") + ",<br />"
			
			+ "&nbsp;muted: " + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "muted") + ",<br />"

			+ "&nbsp;backgroundColor: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "backgroundColor") + "',<br />"

			+ "&nbsp;cssSelectorAncestor: '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "cssSelectorAncestor") + "',<br />"

			+ "&nbsp;cssSelector: {";

			var cssSelector = $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "cssSelector");
			for(prop in cssSelector) {
				
				// jPlayerInfo += "<br />&nbsp;&nbsp;" + prop + ": '" + cssSelector[prop] + "'," // This works too of course, but want to use option method for deep keys.
				jPlayerInfo += "<br />&nbsp;&nbsp;" + prop + ": '" + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "cssSelector." + prop) + "',"
			}

			jPlayerInfo = jPlayerInfo.slice(0, -1); // Because the sloppy comma was bugging me.

			jPlayerInfo += "<br />&nbsp;},<br />"

			+ "&nbsp;errorAlerts: " + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "errorAlerts") + ",<br />"
			
			+ "&nbsp;warningAlerts: " + $(this).data("jPlayerInspector").jPlayer.jPlayer("option", "warningAlerts") + "<br />"

			+ "});</code></p>";
			$(this).data("jPlayerInspector").configJq.html(jPlayerInfo);
			return this;
		},
		updateStatus: function() { // This displays information about jPlayer's status in the inspector
			$(this).data("jPlayerInspector").statusJq.html(
				"<p>jPlayer is " +
				($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.paused ? "paused" : "playing") +
				" at time: " + Math.floor($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.currentTime*10)/10 + "s." +
				" (d: " + Math.floor($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.duration*10)/10 + "s" +
				", sp: " + Math.floor($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.seekPercent) + "%" +
				", cpr: " + Math.floor($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.currentPercentRelative) + "%" +
				", cpa: " + Math.floor($(this).data("jPlayerInspector").jPlayer.data("jPlayer").status.currentPercentAbsolute) + "%)</p>"
			);
			return this;
		}
	};
	$.fn.jPlayerInspector = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.jPlayerInspector' );
		}    
	};
})(jQuery);
