
	function stripslashes(str) {
		str=str.replace(/\\'/g,'\'');
		str=str.replace(/\\"/g,'"');
		str=str.replace(/\\0/g,'\0');
		str=str.replace(/\\\\/g,'\\');
		return str;
	}

	function setvid(file,flashid,title,height,width) {
		  
		var obj = '<object type="application/x-shockwave-flash" ';
		obj += '	data="plugins/mymuse/vidplayer_dewplayer/vidplayers/dewtube.swf" width="'+width+'" height="'+height+'" ';
		obj += '	id="flash_1">';
		obj += '	<param name="allowFullScreen" value="true" />';
		obj += '	<param name="movie" value="plugins/mymuse/vidplayer_dewplayer/vidplayers/dewtube.swf" />';
		obj += '<param name="quality" value="high" />';
		obj += '<param name="bgcolor" value="#FF0066" />';
		obj += '<param name="flashvars" value="movie='+file+'&amp;autostart=1&height='+height+'&width='+width+'" />';
		obj += '</object>';

            
            		  
		  var cell1 = document.getElementById("dewtube_player");		
		  cell1.innerHTML=obj;

		  var cell2 = document.getElementById("playing_title");
		  title = stripslashes(title);
		  cell2.innerHTML=title;
		  
		}

	function setvid2(file,height,width) {

		var obj = '<object type="application/x-shockwave-flash" ';
		obj += '	data="plugins/mymuse/vidplayer_dewplayer/vidplayers/dewtube.swf" width="'+width+'" height="'+height+'" ';
		obj += '	id="flash_1">';
		obj += '	<param name="allowFullScreen" value="true" />';
		obj += '	<param name="movie" value="plugins/mymuse/vidplayer_dewplayer/vidplayers/dewtube.swf" />';
		obj += '<param name="quality" value="high" />';
		obj += '<param name="bgcolor" value="#FF0066" />';
		obj += '<param name="flashvars" value="movie='+file+'&amp;autostart=1&height='+height+'&width='+width+'" />';
		obj += '</object>';
		  
		  var cell1 = document.getElementById("dewtube_player");		
		  cell1.innerHTML=obj;

		  
		}
