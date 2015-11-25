import socket

UDP_IP = "255.255.255.255"
UDP_PORT = 5005
MESSAGE = "Hello, World!"

# print "UDP target IP:", UDP_IP
# print "UDP target port:", UDP_PORT
# print "message:", MESSAGE

print "What is my local IP?"

sock = socket.socket(socket.AF_INET, # Internet
                             socket.SOCK_DGRAM) # UDP
sock.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
sock.sendto(MESSAGE, (UDP_IP, UDP_PORT))

data, addr = sock.recvfrom(1024)
UDP_IP, UDP_PORT = data.split("||")
print "My ip is:", UDP_IP
print "My port is:", UDP_PORT
