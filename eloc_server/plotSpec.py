import pylab as plt
import matplotlib.pyplot as pl, mpld3
from scipy import signal
from scipy import fftpack
import numpy as np


def plotSpec(d1,d2,fs):
    ln1=np.size(d1)
    ln2=np.size(d2)
    print ln1,ln2
    fs =fs*1.0
    time_s=ln1/fs

    fig, ax = pl.subplots(6)

    #d1 = signal.resample(d1, int(ln1 * 600.0 / fs))  #
    #d2 = signal.resample(d2, int(ln2 * 600.0 / fs))  #

    pl.subplot(321)
    pl.plot(d1)
    pl.subplot(322)
    pl.plot(d2)

    pl.subplot(323)
    pxx, freq, t, cax = pl.specgram(d1, Fs=fs)
    pl.subplot(324)
    pxx, freq, t, cax = pl.specgram(d2, Fs=fs)

    pl.subplot(325)
    yf = fftpack.fft(d1)
    xf = fftpack.fftfreq(ln1, 1 / fs)
    yf = 2.0 / ln1 * np.abs(yf[0:ln1 / 2])
    pl.plot(xf[1:int(100 * time_s)], yf[1:int(100 * time_s)])
    pl.subplot(326)
    yf = fftpack.fft(d2)
    xf = fftpack.fftfreq(ln2, 1 / fs)
    yf = 2.0 / ln2 * np.abs(yf[0:ln2 / 2])
    pl.plot(xf[1:int(100 * time_s)], yf[1:int(100 * time_s)])
    pl.show()

    #html=mpld3.fig_to_html(fig)
    #return html



