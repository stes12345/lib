using System;
using System.Diagnostics;
using System.Linq;
using System.Reflection;

namespace RootNamespace.Persistence
{
    public class ProcessCommon
    {
        //private static readonly ILog Log = LogManager.GetLogger(System.Reflection.MethodBase.GetCurrentMethod().DeclaringType);
        private static readonly Logger Log = new Logger(System.Reflection.MethodBase.GetCurrentMethod().DeclaringType);

        public static void KillProcessesByName(string processName)
        {
            foreach (var process in Process.GetProcessesByName(processName))
            {
                process.Kill();
            }

        }

        public static void KillOldInstances()
        {
            var pname = Process.GetProcessesByName(AppDomain.CurrentDomain.FriendlyName.Remove(AppDomain.CurrentDomain.FriendlyName.Length - 4));
            if (pname.Length > 1)
            {
                var myProcessId = Process.GetCurrentProcess().Id;
                pname.First(p => p.Id != myProcessId).Kill();
            }

        }

        public static string GetAppName()
        {
            return Assembly.GetExecutingAssembly().GetName().Name;
        }
    }
}
