using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Diagnostics;
using System.Reflection;
using MySql.Data.MySqlClient;

namespace RootNamespace.Persistence
{
    internal class MySqlCommon
    {
        //private static readonly ILog Log = LogManager.GetLogger(System.Reflection.MethodBase.GetCurrentMethod().DeclaringType);
        private static readonly Logger Log = new Logger(MethodBase.GetCurrentMethod().DeclaringType);

        private static MySqlConnection Connection { get; set; }

        public static MySqlCommand GetCommand(string connectionString, string sql, IReadOnlyCollection<MySqlParameter> parameters)
        {
            Connect(connectionString);

            var command = new MySqlCommand
            {
                Connection = Connection,
                CommandText = sql
            };
            command.Prepare();
            if (parameters != null)
            {
                foreach (var mySqlParameter in parameters)
                {
                    command.Parameters.Add(mySqlParameter);
                }
            }

            return command;
        }

        private static void Connect(string connectionString)
        {
            if (Connection == null || Connection.State != ConnectionState.Open)
            {
                Log.Info($"mysql connection started");
                var stopwatch = new Stopwatch();
                stopwatch.Start();
                Connection = new MySqlConnection
                {
                    ConnectionString = ConfigurationManager.ConnectionStrings[connectionString].ConnectionString
                };
                Connection.Open();
                stopwatch.Stop();
                Log.Info($"mysql connected for {stopwatch.ElapsedMilliseconds} milliseconds ");
            }
        }

        //public static bool Ping(string connectionString)
        //{
        //    Connect(connectionString);
        //    return Connection.Ping();
        //}
    }
}
