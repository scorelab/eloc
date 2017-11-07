import os
#from sklearn.externals import joblib
#from python_speech_features import mfcc
import numpy as np
#import array
import time
import socket
from scipy import signal as sig 
import pylab as pl
#from scipy import fftpack
import signal 

SYSTEM_RATE='600'
TIMEOUT=2
node_id=123456789
MAX=24000
fs_expect=int(SYSTEM_RATE)
time_sampling=0
#a=[]
#b=[]

#clf = joblib.load('/media/sdc/svm/Model/model_'+ SYSTEM_RATE +'.pkl')
#--------UDP socket-------
HOST, PORT = "10.22.196.21", 9998
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
#-------TCP socket--------
TCP_IP = '192.168.1.41' #'10.22.196.21'
TCP_PORT = int(raw_input("port : "))
#s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

#----------------------------------------------------
def extract_features(data,fs):
    mfcc_feat = mfcc(data,fs,winlen=0.25 ,winstep=0.1,lowfreq=14, highfreq=100)
    return mfcc_feat
#------------------------------------------
def plotSpec(d1,d2,fs2):
    ln1=len(d1)
    ln2=len(d2)
    fs2=fs2*1.0
    time_s=ln1/fs2

    pl.subplot(321)
    pl.plot(d1)
    pl.subplot(322)
    pl.plot(d2)

    pl.subplot(323)
    pxx,  freq, t, cax = pl.specgram(d1, Fs=fs2)
    pl.subplot(324)
    pxx,  freq, t, cax = pl.specgram(d2, Fs=fs2)
    
    pl.subplot(325)
    yf = fftpack.fft(d1)
    xf=fftpack.fftfreq(ln1,1/fs2)
    yf=2.0/ln1 * np.abs(yf[0:ln1/2])
    pl.plot(xf[1:int(100*time_s)],yf[1:int(100*time_s)])
    pl.subplot(326)
    yf = fftpack.fft(d2)
    xf=fftpack.fftfreq(ln2,1/fs2)
    yf=2.0/ln2 * np.abs(yf[0:ln2/2])
    pl.plot(xf[1:int(100*time_s)],yf[1:int(100*time_s)])    
    pl.show()

#------------------------------------------

class TimeoutError(Exception):
    pass

def handle_timeout(signum, frame):
    import errno
    raise TimeoutError(os.strerror(errno.ETIME))

def sendData(data1):
    #-------UDP----------	
    '''''
    print "--- sending via UDP ---"
    global HOST,PORT,sock
    i=0
    s=len(data1)
    while 1:
	#print "i:",i," s:",s
	if i>s:
	    sock.sendto(data1[i:].tostring(), (HOST, PORT))
	    break
    	sock.sendto(data1[i:i+1024].tostring(), (HOST, PORT))
	i=i+1024
    print "data sent"
    received = sock.recv(1024)
    print "Received: {}".format(received)
    '''''
    #-------tcp--------
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    ##print "--- sending via TCP ---"
    global TCP_IP, TCP_PORT
    i=0
    ln=len(data1)
    s.connect((TCP_IP, TCP_PORT))
    while 1:
        #print "i:",i," s:",ln
        if i>ln:
            s.send(data1[i:].tostring())
            break
        s.send(data1[i:i+1024].tostring())
        i=i+1024
    ##print "data sent"
    received = s.recv(1024)
    #print "Received: {}".format(received)
    s.close()
#--------------------------------------------------------

def analogRead():
    ADC_PATH= os.path.normpath('/proc/')
    ADC_FILENAME = "adc"
    global a,b,time_sampling
    c=[]
    d=[]
    time_sample=0
    fdA = open('/proc/adc2', 'r')
    fdB = open('/proc/adc3', 'r')    
    start=time.time()
    for i in range(MAX):
    	fdA.seek(0)
    	fdB.seek(0)
        c.append((int(fdA.read(16)[5:])-2100)/100.0)
	d.append((int(fdB.read(16)[5:])-2100)/100.0)
     	#time.sleep(5.0/10000000)
    time_sampling=time.time()-start
    #print "A : ",len(c),", B : ",len(d),", time : ",time_sampling
    fdA.close()
    fdB.close()
    fs=len(c)*1.0/time_sampling
    samples=int(fs_expect*time_sampling)
    c=sig.resample(c,samples)
    d=sig.resample(d,samples)
    #features=extract_features(c,fs_expect)
    #res=clf.predict(features)
    #print res
    #print c.dtype
    if 1: #if detected
	##print "sending data"
	signal.alarm(TIMEOUT)
	try:
	    sendData(np.append(len(c)*2,np.append(node_id,np.append(start,np.append(c,d)))))
	except TimeoutError:
    	    print "Timeout reached"
	except:
	    print "network error" 
	finally:
    	    signal.alarm(0)    
#a=c
    #b=d
    ##print "len_expected : ",MAX,", len_resampled : ",len(c),"fs_real : ",fs,"spectrum_fs : ",fs_expect," time : ",time_sampling
    ##return time_sampling    
    #time.sleep(1)
#--------------------main------------------------------
in1=int(raw_input("Enter time to collect Data : \n"))
t=time.time()
itr=0

while(itr<in1):
    tr=time.time()
    signal.signal(signal.SIGALRM, handle_timeout)
    t_s=analogRead()   
    itr=itr+1
    print "time per round :",time.time()-tr,"\n"
    time.sleep(1)
t1=time.time()
time_taken=t1-t
print "time taken : ",time_taken,"\n"
#plotSpec(a,b,600)
#----------------------------------------------------------
