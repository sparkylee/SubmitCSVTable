import requests
import os
def main():	
	myTest(TestCase1());
	myTest(TestCase2());
	
def myTest(tc):
	  r = requests.post(tc.url, data=tc.data,files=tc.files);
	  print(r.status_code, r.reason);
	  #print(r.text);
	  tc.test(r.text);
	
class TestCase1: ###successfull case
	url    = "http://localhost/upload.php";
	data =  {'submit': 1, 'uname': 'wave', 'psw': 'bestcompany'};
	files  = {'fileToUpload':  open('data_example.csv', 'rb') };
	def test(self,rtext):
		print(rtext);
		return True;
	
class TestCase2:  # wrong case1
	url    = "http://localhost/upload.php";
	data =  {'submit': 1, 'type': 'issue', 'action': 'show'};
	files  = {'file': open('data_example.csv', 'rb')};
	def test(self,rtext):	
		print(rtext);
		return True;
	
######
main();
