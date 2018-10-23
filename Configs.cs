using System.Collections.Generic;
using System.Collections.Specialized;
using System.Configuration;
using System.Reflection;

namespace RootNamespace
{
    /**
     * config reader
     */
    public static class Configs
    {
        private static readonly Dictionary<string, string> ConfigSections = new  Dictionary<string, string>();

        public static string GetConfigValue(string section, string key)
        {
            if (ConfigSections.Count == 0)
            {
                var settings = ConfigurationManager.GetSection(section) as NameValueCollection;
                if (settings != null)
                {
                    foreach (var key1 in settings.AllKeys)
                    {
                        ConfigSections[key1] = settings[key1];
                    }
                }
                else 
                {
                    // https://stackoverflow.com/questions/8656317/comvisible-net-assembly-and-app-config
                    var filename = Assembly.GetExecutingAssembly().Location;
                    var configuration = ConfigurationManager.OpenExeConfiguration(filename);
                    var sections = configuration.AppSettings.Settings;
                    foreach (KeyValueConfigurationElement keyValue in sections)
                    {
                        ConfigSections[keyValue.Key] = keyValue.Value;
                    }
                }
            }
            return ConfigSections[key];
        }
    }
}
