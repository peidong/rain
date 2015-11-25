import socket

UDP_PORT = 5005

sock = socket.socket(socket.AF_INET, # Internet
                     socket.SOCK_DGRAM) # UDP
sock.bind(('', UDP_PORT))

while True:
    data, addr = sock.recvfrom(1024) # buffer size is 1024 bytes
    print "Successfully receive the package"
    print "Sending back to the asker"
    asker_UDP_IP = addr[0]
    asker_UDP_PORT = addr[1]
    MESSAGE = str(asker_UDP_IP)+"||"+str(asker_UDP_PORT)
    sock.sendto(MESSAGE, (asker_UDP_IP, asker_UDP_PORT))
