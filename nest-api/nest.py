#!/usr/bin/env python
import time
import requests
import json
import yaml
import threading
#http://mysql-python.sourceforge.net/MySQLdb.html
import MySQLdb

results_raw = requests.get("https://developer-api.nest.com/devices.json?auth=c.Q2TYtjcqbU1Ypr9J5I2dQEsmXHaHFbiFIMrqh67jrMlsbiNEIdvMIEHMAAazWhh49YuCEzk1VqE4gAsoasOjgpJLl8xo2KhadllPeJZNlNttb0t61SXgNWAvZOQLupxXDjpWz3MxEj8INiPU")
results_json = results_raw.text
results = yaml.safe_load(results_json)

humidity = int(results['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['humidity'])
current_temperature = int(results['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['ambient_temperature_f'])

print ("humidity = " + str(humidity))
print ("current_temperature = " + str(current_temperature))

db=MySQLdb.connect(host="localhost",user="webmaster",db="nest")
db.autocommit(True)
cur = db.cursor()

cur.execute("UPDATE thermostat SET current_temperature = %s, humidity = %s, update_time = now() WHERE id = 1", (str(current_temperature), str(humidity)))

cur.execute("SELECT * FROM thermostat")
for row in cur.fetchall():
    print row[0]
    print row[1]
    print row[2]
    print row[3]
