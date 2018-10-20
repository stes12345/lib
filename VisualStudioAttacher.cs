//#if DEBUG

using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices;
using System.Runtime.InteropServices.ComTypes;
using System.Threading;
using EnvDTE;
using EnvDTE100;
using EnvDTE80;
using DTEProcess = EnvDTE.Process;
using Process = System.Diagnostics.Process;

namespace theapp.Helpers
{
    #region Classes

    public class VisualStudioAttacher : Microsoft.VisualStudio.Shell.Package
    {
        // based on https://pastebin.com/KKyBpWQW https://codegists.com/code/debugging-attach-to-process-visual-studio/  [Apartment(ApartmentState.STA)]
        public const string theappExecutable = @"theapp";
        public const string IisExecutable = "iisexpress.exe";
        public const string theappPath = @"\theapp\bin\Debug\";

        #region Public Methods

        [DllImport("ole32.dll")]
        public static extern int CreateBindCtx(int reserved, out IBindCtx ppbc);

        [DllImport("ole32.dll")]
        public static extern int GetRunningObjectTable(int reserved, out IRunningObjectTable prot);

        /// <summary>
        /// Attaches IISExpress and host service to the debugger
        /// </summary>
        /// <param name="path"></param>
        /// <param name="processImageName"></param>
        /// <param name="theappName"></param>
        public static void AttachVisualStudioToProcess(string path, string processImageName, string theappName)
        {
            _DTE visualStudioInstance;
            Debugger5 dbg5;
            Transport trans;

            OleMessageFilter.Register();

            ConnectDebugger(out dbg5, out trans, out visualStudioInstance);

            var dbgeng = trans.Engines.Item("Managed (v4.6, v4.5, v4.0)");

            if (!string.IsNullOrEmpty(theappName))
            {
                TryStopProcess(processImageName, theappName, false, dbg5, trans);

                var thisLocation = Path.GetDirectoryName(path);
                if (string.IsNullOrEmpty(thisLocation))
                {
                    return;
                }
                const string baseDirectory = @"\src\";
                var exelPath = thisLocation.Substring(0,
                                   thisLocation.LastIndexOf(baseDirectory, StringComparison.CurrentCultureIgnoreCase) +
                                   baseDirectory.Length) + theappPath + theappName + ".exe";
                var process = Process.Start(exelPath);
                foreach (DTEProcess proc in dbg5.LocalProcesses)
                {
                    if (process == null || proc.ProcessID != process.Id)
                    {
                        continue;
                    }
                    ((Process2)proc).Attach2(dbgeng);
                    break;
                }

            }
            var proc2 = (Process2)dbg5.GetProcesses(trans, Environment.MachineName).Item(processImageName);
            proc2.Attach2(dbgeng);

            OleMessageFilter.Revoke();
        }

        /// <summary>
        /// Finds debugger and transport
        /// </summary>
        /// <param name="dbg5"></param>
        /// <param name="trans"></param>
        /// <param name="visualStudioInstance"></param>
        private static void ConnectDebugger(out Debugger5 dbg5, out Transport trans, out _DTE visualStudioInstance)
        {
            visualStudioInstance = null;
            dbg5 = null;
            trans = null;

            var visualStudioProcesses = GetVisualStudioProcesses();

            foreach (var visualStudioProcess in visualStudioProcesses)
            {
                if (!TryGetVsInstance(visualStudioProcess.Id, out visualStudioInstance))
                {
                    continue;
                }
                dbg5 = (Debugger5)visualStudioInstance.Debugger;
                trans = dbg5.Transports.Item("Default");
                break;
            }

        }

        /// <summary>
        /// tries to detach IIS and to stop host service
        /// </summary>
        /// <param name="processImageName"></param>
        /// <param name="theappName"></param>
        /// <param name="detachAll"></param>
        /// <param name="dbg5"></param>
        /// <param name="trans"></param>
        public static void TryStopProcess(string processImageName, string theappName, bool detachAll, Debugger5 dbg5 = null, Transport trans = null)
        {
            var dteInitialized = false;
            //if (dbg5 == null)
            //{
            //    OleMessageFilter.Register();
            //    _DTE visualStudioInstance;
            //    ConnectDebugger(out dbg5, out trans, out visualStudioInstance);
            //    dteInitialized = true;
            //}
            //if (dbg5 == null)
            //{
            //    return;
            //}
            //foreach (var process4 in dbg5.LocalProcesses)
            //{
            //    var process5 = (Process2)process4;
            //    try
            //    {
            //        Debug.WriteLine(process5.DTE.FullName);
            //        //process5.Terminate();
            //        //process5.Detach();.IsBeingDebugged
            //    }
            //    catch (Exception e)
            //    {
            //        Console.WriteLine(e);
            //    }
            //}
            //foreach (var process in Process.GetProcessesByName(theappName, Environment.MachineName))
            //{
            //    process.Kill();
            //}
            var processes = Process.GetProcesses().ToList().Where(p => p.ProcessName == theappExecutable);
            foreach (var process in processes.ToList())
            {
                try
                {
                    process.Kill();
                }
                catch
                {
                    //
                }
            }
            if (detachAll)
            {
                if (dbg5 == null)
                {
                    OleMessageFilter.Register();
                    _DTE visualStudioInstance;
                    ConnectDebugger(out dbg5, out trans, out visualStudioInstance);
                    dteInitialized = true;
                }
                if (dbg5 == null)
                {
                    return;
                }
                //foreach (var process4 in dbg5.LocalProcesses)
                //{
                //    var process5 = process4 as Process2;
                //    try
                //    {
                //        process5?.Terminate();
                //    }
                //    catch (Exception e)
                //    {
                //        Console.WriteLine(e);
                //    }
                //}
                dbg5.DetachAll();
                //var proc2 = (Process2)dbg5.GetProcesses(trans, Environment.MachineName).Item(processImageName);
                //proc2.Detach();
            }
            if (dteInitialized)
            {
                OleMessageFilter.Revoke();
            }
        }

        #endregion

        #region Private Methods

        private static IEnumerable<Process> GetVisualStudioProcesses()
        {
            var processes = Process.GetProcesses();
            return processes.Where(o => o.ProcessName.Contains("devenv"));
        }

        private static bool TryGetVsInstance(int processId, out _DTE instance)
        {
            IntPtr numFetched = IntPtr.Zero;
            IRunningObjectTable runningObjectTable;
            IEnumMoniker monikerEnumerator;
            IMoniker[] monikers = new IMoniker[1];

            GetRunningObjectTable(0, out runningObjectTable);
            runningObjectTable.EnumRunning(out monikerEnumerator);
            monikerEnumerator.Reset();

            while (monikerEnumerator.Next(1, monikers, numFetched) == 0)
            {
                IBindCtx ctx;
                CreateBindCtx(0, out ctx);

                string runningObjectName;
                monikers[0].GetDisplayName(ctx, null, out runningObjectName);

                object runningObjectVal;
                runningObjectTable.GetObject(monikers[0], out runningObjectVal);

                if (runningObjectVal is _DTE && runningObjectName.StartsWith("!VisualStudio"))
                {
                    int currentProcessId = int.Parse(runningObjectName.Split(':')[1]);

                    if (currentProcessId == processId)
                    {
                        instance = (_DTE)runningObjectVal;
                        return true;
                    }
                }
            }

            instance = null;
            return false;
        }

        #endregion
    }

    internal class OleMessageFilter : IOleMessageFilter
    {
        public static void Register()
        {

            if (System.Threading.Thread.CurrentThread.GetApartmentState() == ApartmentState.STA)
            {
                IOleMessageFilter oldFilter;
                CoRegisterMessageFilter(new OleMessageFilter(), out oldFilter);
            }
            else
            {
                throw new COMException("Unable to register message filter because the current thread apartment state is not STA.");
            }
        }

        public static void Revoke()
        {
            IOleMessageFilter oldFilter;
            CoRegisterMessageFilter(null, out oldFilter);
        }

        int IOleMessageFilter.HandleInComingCall(
            int dwCallType,
            IntPtr hTaskCaller,
            int dwTickCount,
            IntPtr lpInterfaceInfo)
        {
            return (int)SERVERCALL.SERVERCALL_ISHANDLED;
        }

        int IOleMessageFilter.RetryRejectedCall(
            IntPtr hTaskCallee,
            int dwTickCount,
            int dwRejectType)
        {
            if (dwRejectType == (int)SERVERCALL.SERVERCALL_RETRYLATER)
            {
                return 99;
            }

            return -1;
        }

        int IOleMessageFilter.MessagePending(
            IntPtr hTaskCallee,
            int dwTickCount,
            int dwPendingType)
        {
            return (int)PENDINGMSG.PENDINGMSG_WAITDEFPROCESS;
        }

        [DllImport("Ole32.dll")]
        private static extern int CoRegisterMessageFilter(
            IOleMessageFilter newFilter,
            out IOleMessageFilter oldFilter);
    }

    #endregion

    internal enum SERVERCALL
    {
        SERVERCALL_ISHANDLED = 0,
        SERVERCALL_REJECTED = 1,
        SERVERCALL_RETRYLATER = 2
    }

    internal enum PENDINGMSG
    {
        PENDINGMSG_CANCELCALL = 0,
        PENDINGMSG_WAITNOPROCESS = 1,
        PENDINGMSG_WAITDEFPROCESS = 2
    }

    [ComImport, Guid("00000016-0000-0000-C000-000000000046"),
    InterfaceTypeAttribute(ComInterfaceType.InterfaceIsIUnknown)]
    internal interface IOleMessageFilter
    {
        [PreserveSig]
        int HandleInComingCall(
            int dwCallType,
            IntPtr hTaskCaller,
            int dwTickCount,
            IntPtr lpInterfaceInfo);

        [PreserveSig]
        int RetryRejectedCall(
            IntPtr hTaskCallee,
            int dwTickCount,
            int dwRejectType);

        [PreserveSig]
        int MessagePending(
            IntPtr hTaskCallee,
            int dwTickCount,
            int dwPendingType);
    }
}

//#endif