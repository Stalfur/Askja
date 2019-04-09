<script>
    var currUrl = "<?php echo $_SERVER["REQUEST_URI"]; ?>";
	var baseUrl = "<?php echo $_SERVER["SCRIPT_NAME"]; ?>";
	
function addParameter(url, param, value) {
    var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))'),
        parts = url.toString().split('#'),
        url = parts[0],
        hash = parts[1]
        qstring = /\?.+$/,
        newURL = url;

    if (val.test(url)) { newURL = url.replace(val, '$1' + param + '=' + value); }
    else if (qstring.test(url)) { newURL = url + '&' + param + '=' + value; }
    else { newURL = url + '?' + param + '=' + value; }
    if (hash) { newURL += '#' + hash; }
    return newURL;
}

<?php $ajaxurl = "ajax/settlement_area.php"; ?>
function radioClick(id, field, value, did, addcss)
{
	$.ajax({
		url : '<?= $ajaxurl; ?>',
		data : 'id='+id+'&field='+field+'&value='+value,
		type : 'POST',
		success: function(data) {
                    if(data != 1){
//                        alert("Updated!");
//                    }else{
                        alert("Error. Not logged in?");
                    }
                },
		error : function() {
			alert('<?php echo _errorupdating; ?>');
		}
	});
    toggleDropdown(did, addcss);
}

function imageryClick(id, value)
{
    switch (value)
    {
        case 100:
            document.getElementById("goodImagery").style.backgroundColor = "white";
            break;
        case 50:
            document.getElementById("partialImagery").style.backgroundColor = "white";
            break;
        case 0:
            document.getElementById("badImagery").style.backgroundColor = "white";
            break;
        default: break;
    }
        $.ajax({
                url : '<?= $ajaxurl; ?>',
                data : 'id='+id+'&field=imagery&value='+value,
                type : 'POST',
                success: function(data) {
                    if(data != 1){
//                       
                        alert("Error. Not logged in?");
                    } else  {
                        location.reload(true);
                    }
                },
                error : function() {
                        alert('<?php echo _errorupdating; ?>');
                }
        });
    
 //   location.reload(true);
}

function toggleDropdown(id, addcss)
{   
    var bg = document.getElementById(id);
    
    if (document.getElementById(id).classList.contains("dropdown-min"))
    {
        document.getElementById(id).classList.remove("dropdown-min");
        document.getElementById(id).classList.add(addcss);
        bg.style.width = "240px";
        if (addcss === "dropdown-3")
        {
            bg.style.height = "170px";
        }
        else if (addcss === "dropdown-4")
        {
            bg.style.height = "212px";
        }
    }
    else
    {
        document.getElementById(id).classList.remove(addcss);
        document.getElementById(id).classList.add("dropdown-min");
        bg.style.width = "34px";
        bg.style.height = "42px";
    }
}
   function toggleFilter(id, name)
   {
       var newValue = document.getElementById(id).value;
       var url = addParameter(currUrl, name, newValue);
       location.assign(url);
   }
   
   function replaceFilter(id, name)
   {
		var newValue = document.getElementById(id).value;
		var url = baseUrl + "?" + name + "=" + newValue;
		location.assign(url);
   }
   
   function iconFilter(name, value)
   {
       var url = addParameter(currUrl, name, value);
       location.assign(url);
   }
</script>