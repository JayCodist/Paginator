// Safely inserts an "urlencoded" attribute into the supplied str and returns 
// the new url query string starting with "?"
function insertUrlAttribute(str, attr, val)
{
	if (typeof attr != "string" || attr == "")
	{
		if (typeof str == "string")
			return str;
		else
			return "";
	}
	if (typeof str != "string" || str == "")
		return "?" + attr + "=" + val;

	//"?pgnumber=2&pglimit=10&search=&sort=0"
	var isNewAttr = true;
	var start = str.indexOf("?") + 1;
    var x = str.indexOf(attr);
    if (x != -1 && str[(x + attr.length)] == "=")
		isNewAttr = false;
    var buffer = "";
    if (!isNewAttr)
    {
    	buffer = (x > start ? str.substring(start, x - 1) : "");
    	var nextAttr = str.indexOf("&", x + 1);
    	if (nextAttr > -1)
    	{
    		buffer += str.substring(nextAttr);
    	}

		buffer += (buffer[buffer.length - 1] == '&' ? "" : "&") + attr + "=" + val;
    }
    else
    	return str + (str == "?" ? "" : "&") + attr + "=" + val;

    if (buffer[0] == "&")
    	buffer = buffer.substring(1);

    return "?" + buffer;

}