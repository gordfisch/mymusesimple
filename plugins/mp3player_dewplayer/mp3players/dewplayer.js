
	function stripslashes(str) {
		str=str.replace(/\\'/g,'\'');
		str=str.replace(/\\"/g,'"');
		str=str.replace(/\\0/g,'\0');
		str=str.replace(/\\\\/g,'\\');
		return str;
	}

	function mymuseset(file,flashid,title) {

		  var dewp = document.getElementById("flash_"+flashid);
		  if(dewp!=null) {
			dewp.dewset(file);
		  }
		  var cell = document.getElementById("jp-title-li");
		  title = stripslashes(title);
		  cell.innerHTML=title;
		}
