# LIMS Middleware

The middleware handles the following functionality:

* Communicating with LIMS using various encryption methods
* Setting up the Nexus hub
* Updating the Nexus hub and middleware
* Simulating LIMS (for test purposes only)

## Using LIMS Middleware

LIMS middleware runs in the background. Handling updates and setup are automatic. Settings for LIMS can be found in the Network tab of the DMS.

To see if LIMS is working as expected, check the LIMS indicator on the bottom right of the web app UI.

## Using LIMS Simulator

To turn on the LIMS Simulator, toggle the setting in the Network tab of the DMS. This changes the LIMS settings to what is required for the simulator to work.

Pressing save reveals a new tab in the settings area and updates the LIMS indictor to show that the simulator is active.

The LIMS simulator tab serves two purposes:
- Viewing logs from LIMS in real time
- Adding data to a dummy database so that the simulator can pretend it is getting real patient information.

Be sure to turn off the LIMS simulator after conducting any tests, as results will not be forwarded to LIMS if this remains on.