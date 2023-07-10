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

    var userFolder = basePath + strippedUname;
    //debug.Text = "\\\\adorad.local\\srvdata\\" + strippedUname;
    if(!Directory.Exists(userFolder))
    {
        fileList.Text = "You have no files!";
    } else {
        fileList.Text = "<ul><li>" + String.Join("</li><li>", Directory.GetFiles(userFolder, "*",  SearchOption.TopDirectoryOnly).Select(x => String.Format("<a href=\"get.aspx?path={0}\">{1}</a>", WebUtility.UrlEncode(x), x.Replace(userFolder + "\\", "")))) + "</li></ul>";
        
    }
}

</script>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
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
        <a href="WorkhorzClient.zip">Download client for Workhorz vacation request system here!</a>
   

      </header>

      <main>
        <h1>Home</h1>
        <h3>Your files</h3>
        <asp:Label id="fileList" runat="server"></asp:Label>
        <asp:Label id="debug" runat="server"></asp:Label>
      </main>




</body>
</html>