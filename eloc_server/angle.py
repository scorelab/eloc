import numpy as np
#from scipy.io import wavfile
import math
from scipy import signal
#from scipy.signal import butter, lfilter
#import matplotlib.pyplot as plt
import scipy.stats as stats
import pylab as pl

fs_exp=48000

def getAngle(ch1,ch2,fs):
    global limit
    l1 = len(ch1)
    d1 =signal.resample(ch1,int(l1*fs_exp*1.0/fs))    #
    d2 =signal.resample(ch2,int(l1*fs_exp*1.0/fs))#
    limit = len(d1)
    fs = fs_exp
    d = 3  # distance between two microphones
    vs = 350.0
    start_window = 0
    end_window = fs/20#2205
    window = fs/20#2205
    reject = 0
    count=0
    arr = []
    while (end_window < limit):
        s1 = d1[start_window:end_window]
        s2 = d2[start_window:end_window]
        xcor = np.correlate(s1, s2, 'full')
        m = max(xcor)
        im = np.argmax(xcor)
        start_window = start_window + window
        end_window = end_window + window
        deference = abs(im - window)
        ang = deference * vs / fs / d
        if (ang <= 1):
            angle = np.arccos(ang)
            arr.append(math.degrees(angle))
            count=count+1
        else:
            reject=reject+1

    #h = sorted(arr)  # sorted
    #fit = stats.norm.pdf(h, np.mean(h), np.std(h))  # this is a fitting indeed
    #pl.plot(h, fit, '-o')
    #pl.hist(h, normed=True)  # use this to draw histogram of your data
    arr1=np.array(arr).round()
    modeData = stats.mode(arr1)
    print"..........angle data............"
    print 'Mode : ', modeData[0], 'Count : ', modeData[1]
    print 'Mean : ', np.mean(arr),' , STD : ', np.std(arr)
    print "Data presentage : ",count*100/(count+reject)
    #pl.show()  # use may also need add this
    return modeData[0]



