using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Net;
using System.Text;

namespace RootNamespace
{
    public class HttpUploadFile
    {
        public static void Upload(string url, NameValueCollection nvc, List<UploadFile> memoryStream)
        {
            //log.Debug(string.Format("Uploading {0} to {1}", fileName, url));
            var boundary = "---------------------------" + DateTime.Now.Ticks.ToString("x");
            var boundarybytes = Encoding.ASCII.GetBytes("\r\n--" + boundary + "\r\n");

            var wr = (HttpWebRequest)WebRequest.Create(url);
            wr.ContentType = "multipart/form-data; boundary=" + boundary;
            wr.Method = "POST";
            wr.KeepAlive = true;
            wr.Credentials = CredentialCache.DefaultCredentials;

            //allows for validation of SSL certificates 
            ServicePointManager.ServerCertificateValidationCallback = (sender, certificate, chain, errors) => true; 

            var rs = wr.GetRequestStream();

            const string formdataTemplate = "Content-Disposition: form-data; name=\"{0}\"\r\n\r\n{1}";
            rs.Write(boundarybytes, 0, boundarybytes.Length);
            foreach (string key in nvc.Keys)
            {
                var formItem = string.Format(formdataTemplate, key, nvc[key]);
                var formItemBytes = Encoding.UTF8.GetBytes(formItem);
                rs.Write(formItemBytes, 0, formItemBytes.Length);
                rs.Write(boundarybytes, 0, boundarybytes.Length);
            }
            if (memoryStream != null && memoryStream.Count > 0)
            {
                foreach (var uploadFile in memoryStream)
                {
                    const string headerTemplate = "Content-Disposition: form-data; name=\"{0}\"; filename=\"{1}\"\r\nContent-Type: {2}\r\n\r\n";
                    var header = string.Format(headerTemplate, uploadFile.ParamName, uploadFile.FileName, uploadFile.ContentType);
                    var headerBytes = Encoding.UTF8.GetBytes(header);
                    rs.Write(headerBytes, 0, headerBytes.Length);

                    //FileStream fileStream = new FileStream(fileName, FileMode.Open, FileAccess.Read);
                    var buffer = new byte[4096];
                    int bytesRead;
                    while ((bytesRead = uploadFile.MemoryStream.Read(buffer, 0, buffer.Length)) != 0)
                    {
                        rs.Write(buffer, 0, bytesRead);
                    }
                    uploadFile.MemoryStream.Close();
                }
            }

            var trailer = Encoding.ASCII.GetBytes("\r\n--" + boundary + "--\r\n");
            rs.Write(trailer, 0, trailer.Length);
            rs.Close();

            WebResponse wresp = null;
            try
            {
                wresp = wr.GetResponse();
                var stream2 = wresp.GetResponseStream();
                if (stream2 != null)
                {
                    var reader2 = new StreamReader(stream2);
                    Console.WriteLine(@"File uploaded, server response is: |" + reader2.ReadToEnd() + "|");
                }
                else
                {
                    Console.WriteLine(@"no response");
                }
            }
            catch (Exception)
            {
                //log.Error("Error uploading fileName", ex);
                if (wresp != null)
                {
                    wresp.Close();
                    wresp = null;
                }
            }
            finally
            {
                wr = null;
            }
            /*NameValueCollection nvc = new NameValueCollection();
            nvc.Add("id", "TTR");
            nvc.Add("btn-submit-photo", "Upload");
            Upload("http://your.server.com/upload",
                 @"C:\test\test.jpg", "fileName", "image/jpeg", nvc);*/
        }
    }
}
