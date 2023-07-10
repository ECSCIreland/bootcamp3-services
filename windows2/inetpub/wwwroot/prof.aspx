<%@ Page Language="C#" %>
<%@ import Namespace="System.IO" %>
<%@ import Namespace="System.Diagnostics" %>
<%@ import Namespace="System.Linq" %>
<%@ import Namespace="System.Net" %>

<script runat="server">      

protected void Page_Load(object sender, EventArgs e)
{
    var basePath = "\\\\dc1.adorad.local\\srvdata\\";;
    var currIdentity = ((System.Security.Principal.WindowsIdentity)User.Identity);
    var uname = currIdentity.Name;
    userInfo.Text = "Authenticated as <b>" + currIdentity.Name + "</b> (ImpersonationLevel: " + currIdentity.ImpersonationLevel.ToString() + ")";
    var strippedUname = uname.Split('\\')[1];

    
    //debug.Text = "\\\\adorad.local\\srvdata\\" + strippedUname;

    if(String.IsNullOrEmpty(Request.QueryString["uname"]))
    {
        fileList.Text = "<ul><li>" + String.Join("</li><li>", Directory.GetDirectories(basePath, "*",  SearchOption.TopDirectoryOnly).Select(x => String.Format("<a href=\"prof.aspx?uname={0}\">{1}</a>",  WebUtility.UrlEncode(x.Replace(basePath, "")), x.Replace(basePath, "")))) + "</li></ul>";
        heading.Text = "<h3>Choose user</h3>";
    } else {
        var userFolder = basePath + Request.QueryString["uname"];
        fileList.Text = "<ul><li>" + String.Join("</li><li>", Directory.GetFiles(userFolder, "*",  SearchOption.TopDirectoryOnly).Select(x => String.Format("<a href=\"proff.aspx?path={0}\">{1}</a>", WebUtility.UrlEncode(x), x.Replace(userFolder + "\\", "")))) + "</li></ul>";
        heading.Text = "<h3>Choose " +  Request.QueryString["uname"] + "'s file</h3>";   
    }
    
}

</script>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profanity check</title>
        <link rel="stylesheet" href="simple.css">
    </head>
<body>  
    
    <header>
        <p><asp:Label id="userInfo" runat="server"></asp:Label></p>
        <i>Hint: you need Delegation level for the app to work</i>  
        <nav>
            <a href="index.aspx">Home</a>
            <a href="upload.aspx">Upload</a>
            <a href="prof.aspx">Profanity check</a>
        </nav>
        
   

      </header>

      <main>
        <h1>Profanity check</h1>
        <asp:Label id="heading" runat="server"></asp:Label>
        <asp:Label id="fileList" runat="server"></asp:Label>
        <asp:Label id="debug" runat="server"></asp:Label>
      </main>




</body>
</html>