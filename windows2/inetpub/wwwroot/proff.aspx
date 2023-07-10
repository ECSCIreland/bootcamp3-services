<%@ Page Language="C#" %>
<%@ import Namespace="System.IO" %>


<script runat="server">      
protected void Page_Load(object sender, EventArgs e)
{
    var basePath = "\\\\dc1.adorad.local\\srvdata\\";;
    
    string data = File.ReadAllText(Request.QueryString["path"].ToString());

    Response.Write(data.Contains("bollocks") ? "This file cannot be published!!!" : "Everything is fine");

}

</script>