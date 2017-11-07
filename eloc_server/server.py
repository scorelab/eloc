#!/usr/bin/python           # This is server.py file

import socket               # Import socket module
import numpy as np
from scipy.io.wavfile import write
from angle import getAngle
from plotSpec import plotSpec
from os import listdir, mkdir
import sys
import MySQLdb
from dbHandler import createDataTable,insertData
import datetime


# Open database connection
db = MySQLdb.connect("localhost","root","root","elocate" )

now = datetime.datetime.now()
date = now.strftime("%Y_%m_%d")
dt=np.empty((0))

def handle_data(dt):
    end = (len(dt[3:]) / 2)+3
    #for i in range(10):
    #    print dt[i]
    print 'len:', dt[0], '\nnode_ID', dt[1], "\ntimestamp:", dt[2]
    #print end, len(dt), dt.dtype
    if int(dt[0])==len(dt[3:]):  # 0:length of data  1:nodeId 2:timestamp
        print "ch1 : %d  ch2 : %d" % (len(dt[3:end]), len(dt[end:]))
        if not str(int(dt[1])) in listdir('./eloc_data'):
            mkdir('./eloc_data/'+str(int(dt[1]))+'/'+ date)
        path='../eloc_data/'+str(int(dt[1]))+'/'+ date +'/'+str(int(dt[2])) + '.wav'
        print "file path : ",path
        write(path, 600, np.array([dt[3:end], dt[end:]]).T)
        angle=getAngle(dt[3:end],dt[end:],600)
        if insertData(db,int(dt[1]),int(dt[2]),path,angle) :
            print ".....data base insert success...."
        else:
            print "data base insertion error"
        #plotSpec(dt[3:end],dt[end:],600)
    return 1

#---------------- __main__ ---------------

createDataTable(db)

#------------------ TCP ------------------
#'''''
TCP_IP = '192.168.43.61'#'10.22.196.21'
TCP_PORT = 5005
BUFFER_SIZE = 1024  # Normally 1024, but we want fast response
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.bind((TCP_IP, TCP_PORT))
while 1:
    s.listen(1)
    conn, addr = s.accept()
    print 'Connection address:', addr

    while 1:
        data = conn.recv(BUFFER_SIZE)
        if not data: break
        d1 = np.fromstring(data, dtype=float)
        dt = np.append(dt, d1)
        #print len(d1)
        if len(dt) > 0 and int(dt[0]) == len(dt[3:]):
            if handle_data(dt):
                dt = np.empty((0))
                print "dt cleared"
                print "-------------------------------------\n"
                conn.send("----- Success -----")
                conn.close()
                break

'''''

#--------------- UDP -------------------

UDP_IP = "10.22.196.21" #for both UDP and TCP
UDP_PORT = 9998 #for both UDP and TCP
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)  # internet, UDP
sock.bind((UDP_IP, UDP_PORT))

while True:
    data, addr = sock.recvfrom(1024*8)  # buffer size is 1024 bytes
    d1 = np.fromstring(data, dtype=float)
    dt = np.append(dt, d1)
    print len(d1)
    if len(dt)>0 and int(dt[0])==len(dt[3:]):
        if handle_data(dt):
            dt = np.empty((0))
            print "dt cleared"
        sock.sendto('Thank you for data',addr)

'''''


