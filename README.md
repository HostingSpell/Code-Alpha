Admin Clock WHMCS Addon
=======================

This repository demonstrates a simple addon module for WHMCS named **Admin Clock**.

Features
--------
- Displays a server time widget on the WHMCS admin dashboard.
- Provides an admin area page showing how long each admin user has been active for common time frames (e.g. today, this month) using data from `tbladminlog`.
- Counts how many support ticket replies each admin has submitted in those periods.
- Requires no database changes; the addon performs read-only queries.

Installation
------------
1. Copy the `modules/addons/admin_clock` directory into the `modules/addons` directory of your WHMCS installation.
2. In the WHMCS admin area, navigate to **Setup > Addon Modules** and activate *Admin Clock*.
3. The dashboard widget will appear automatically, and a link to view detailed admin activity will be available.

This code is a basic example and may require adjustments to match your WHMCS version or database schema.
