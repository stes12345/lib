using System.IO;

namespace RootNamespace
{
    public class UploadFile
    {
        public string FileName { get; set; }

        public string ParamName { get; set; }
        public string ContentType { get; set; }
        public MemoryStream MemoryStream { get; set; }
    }
}
