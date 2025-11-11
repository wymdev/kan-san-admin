// services/versionCheck.js
import Constants from 'expo-constants';
import { Platform } from 'react-native';
import axios from 'axios';

const API_BASE_URL = 'https://your-api.com/api/v1';
const API_KEY = 'your-api-key';

export const checkForUpdates = async () => {
  try {
    const currentVersionCode = Constants.expoConfig?.version || 1;
    const platform = Platform.OS; // 'android' or 'ios'

    const response = await axios.post(
      `${API_BASE_URL}/version/check`,
      {
        version_code: currentVersionCode,
        platform: platform,
      },
      {
        headers: {
          'X-API-KEY': API_KEY,
          'Content-Type': 'application/json',
        },
      }
    );

    if (response.data.success) {
      return response.data.data;
    }
    
    return null;
  } catch (error) {
    console.error('Version check failed:', error);
    return null;
  }
};

// Usage in App.js
import { checkForUpdates } from './services/versionCheck';
import { Alert, Linking } from 'react-native';

useEffect(() => {
  const checkVersion = async () => {
    const updateInfo = await checkForUpdates();
    
    if (updateInfo && updateInfo.update_required) {
      if (updateInfo.force_update) {
        // Force update - user cannot dismiss
        Alert.alert(
          'Update Required',
          `A new version ${updateInfo.latest_version} is available. Please update to continue using the app.\n\n${updateInfo.release_notes}`,
          [
            {
              text: 'Update Now',
              onPress: () => Linking.openURL(updateInfo.download_url),
            },
          ],
          { cancelable: false }
        );
      } else {
        // Optional update
        Alert.alert(
          'Update Available',
          `Version ${updateInfo.latest_version} is now available.\n\n${updateInfo.release_notes}`,
          [
            {
              text: 'Later',
              style: 'cancel',
            },
            {
              text: 'Update',
              onPress: () => Linking.openURL(updateInfo.download_url),
            },
          ]
        );
      }
    }
  };

  checkVersion();
}, []);
