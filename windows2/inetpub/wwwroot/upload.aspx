<%@ Page Language="C#" %>
<%@ import Namespace="System.IO" %>
<%@ import Namespace="System.Diagnostics" %>
<%@ import Namespace="System.Linq" %>
<%@ import Namespace="System.Net" %>
<%@ import Namespace="System.Security.AccessControl" %>

<script runat="server">      

protected void Page_Load(object sender, EventArgs e)
{
    var basePath = "\\\\dc1.adorad.local\\srvdata\\";;
    var currIdentity = ((System.Security.Principal.WindowsIdentity)User.Identity);
    var uname = currIdentity.Name;
    userInfo.Text = "Authenticated as <b>" + currIdentity.Name + "</b> (ImpersonationLevel: " + currIdentity.ImpersonationLevel.ToString() + ")";
    var strippedUname = uname.Split('\\')[1];

    var userFolder = basePath + strippedUname;
    //Make sure we can impersonate or we will create a broken folder
    if(currIdentity.ImpersonationLevel != System.Security.Principal.TokenImpersonationLevel.Delegation)
    {
        Response.Write("no delegation");
        Response.End();
    }
    if(!Directory.Exists(userFolder))
    {
        Directory.CreateDirectory(userFolder);
        DirectoryInfo dInfo = new DirectoryInfo(userFolder);
        DirectorySecurity dSecurity = new DirectorySecurity();

        dSecurity.AddAccessRule(new FileSystemAccessRule(currIdentity.Owner, FileSystemRights.TakeOwnership, InheritanceFlags.None, PropagationFlags.None, AccessControlType.Allow));

        dInfo.SetAccessControl(dSecurity);

        var impersonationContext = currIdentity.Impersonate();

        dSecurity = dInfo.GetAccessControl();
        dSecurity.SetOwner(currIdentity.Owner);
        dInfo.SetAccessControl(dSecurity);
        dSecurity.AddAccessRule(new FileSystemAccessRule("CREATOR OWNER", FileSystemRights.Write, InheritanceFlags.None, PropagationFlags.None, AccessControlType.Allow));
        dSecurity.AddAccessRule(new FileSystemAccessRule("CREATOR OWNER", FileSystemRights.Read, InheritanceFlags.ObjectInherit, PropagationFlags.None, AccessControlType.Allow));
        dInfo.SetAccessControl(dSecurity);

        impersonationContext.Undo();
    }

    if (Request.Files.Count == 1)
    {
        
        HttpPostedFile httpPostedFile = Request.Files[0];

        int fileLength = httpPostedFile.ContentLength;
        if(fileLength > 1024)
        {
            result.Text = "File too big.";
        } else {
            byte[] buffer = new byte[fileLength];
            httpPostedFile.InputStream.Read(buffer, 0, fileLength);
            var impersonationContext = currIdentity.Impersonate();
            File.WriteAllBytes(Path.Combine(userFolder, Path.GetFileName(httpPostedFile.FileName)), buffer);
            impersonationContext.Undo();
            result.Text = "Uploaded.";
        }
        

    }
    


    

}

</script>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload</title>
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
        <h1>Upload</h1>
        <form enctype="multipart/form-data" action="?operation=upload" method="post">
        <br>Please specify a file: <input type="file" name="file">
        <input type="submit" value="Send">
        </form>
        <b><asp:Label id="result" runat="server"></asp:Label></b>
      </main>




</body>
</html>