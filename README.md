# Notification Manager (local_notification_manager)

**Notification Manager** is a local plugin for Moodle that allows administrators to centrally view, analyze, and manage system notifications for users. By default, Moodle does not provide an interface for administrators to view other users' notifications. This plugin addresses that limitation by offering an intuitive interface with detailed control features.

## Tab Structure and Features

The plugin's management interface is divided into 3 main tabs, corresponding to its core functionalities:

### 1. Dashboard
The dashboard provides visual reports and statistics on the overall notification activity within the system.
- **Time Range Filter:** Administrators can filter statistical data by specific time periods such as the Last 7 Days, Last 30 Days, Last 90 Days, or All Time.
- **Key Metrics:** Displays Total Notifications, Read notifications, Unread notifications, Unread Rate, and Engagement level.
- **Analytics:** Provides insights into Top Users (users receiving the most notifications) and Popular Types (the most common notification components).

### 2. Manage Notifications
This tab allows administrators to look up and manage notifications for specific users.
- **User Search:** Search for and select any user in the system to view their incoming notifications.
- **Status Filter:** Filter the notification list by status: All, Read, or Unread.
- **Notification Details:** Displays the Subject, Message, sending Component, Time Created, and Time Read.
- **Move to Trash:** Allows bulk selecting (Select All) to delete notifications. Selected notifications are moved to the Trash tab instead of being permanently deleted immediately.

### 3. Trash
A temporary storage area for notifications deleted from the Manage tab before they are permanently removed from the database.
- **View Deleted Notifications:** Search for users and view their deleted notifications, including the Time Deleted.
- **Restore Selected:** Select one or more notifications to restore them to their normal state (moving them back to the Manage list).
- **Delete Permanently:** Completely remove selected notifications from the system. This action cannot be undone.
- **Automated Cleanup:** The plugin includes a Scheduled Task (`cleanup_trash_task`) to automatically clean up and permanently delete old, lingering notifications in the trash.

---

**Capabilities:**
Only accounts granted the `local/notification_manager:manage` capability (Site Administrators by default) can access the features of this plugin.
