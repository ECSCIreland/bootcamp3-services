<%@ Page Language="C#" %>
<%@ import Namespace="System.IO" %>


<script runat="server">      
protected void Page_Load(object sender, EventArgs e)
{
    var basePath = "\\\\dc1.adorad.local\\srvdata\\";;
    var currIdentity = ((System.Security.Principal.WindowsIdentity)User.Identity);
    
    var impersonationContext = currIdentity.Impersonate();

    Response.Write(File.ReadAllText(Request.QueryString["path"].ToString()));

    impersonationContext.Undo();

}

</script>