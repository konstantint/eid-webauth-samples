import java.io.*;
import java.security.cert.X509Certificate;
import javax.naming.InvalidNameException;
import javax.naming.ldap.LdapName;
import javax.naming.ldap.Rdn;
import javax.servlet.*;
import javax.servlet.http.*;
import javax.security.auth.x500.X500Principal;

public class EidSample extends HttpServlet {
    public void doGet(HttpServletRequest request, HttpServletResponse response)
        throws ServletException, IOException {
        response.setContentType("text/html");
        PrintWriter out = response.getWriter();
        String cn = getSSLClientCN(request);
        out.println("Hello, " + cn);
    }

    private String getSSLClientCN(HttpServletRequest request) {
        X509Certificate certs[] = 
                (X509Certificate[])request.getAttribute("javax.servlet.request.X509Certificate");
        if (certs == null || certs.length == 0) return "";
        X509Certificate clientCert = certs[0];
        X500Principal subjectDN = clientCert.getSubjectX500Principal();

        String dn = subjectDN.getName();
        try {
       	    LdapName ldapDN = new LdapName(dn);
            System.out.println(dn);
            for(Rdn rdn: ldapDN.getRdns()) {
                System.out.println(rdn.getType());
                System.out.println(rdn.getValue());
                if (rdn.getType().equals("CN")) return rdn.getValue().toString();
            }
            return "";
        }
        catch(InvalidNameException e) {
            return "";
        }
    }
}
