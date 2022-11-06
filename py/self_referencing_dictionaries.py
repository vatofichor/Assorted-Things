#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
GitHub: py-seb
Author: Sebastian Mass

This helper class extends the functionality of a dictionary so that any value of
a key can reference another keys value.
This is achieved with string formating, which gets evaluated and returns the proper
value. The reference string must match the keyname of the referenced pair.

 Example:
   myDictionary = dic({"firstKey": "valueToRef", "secondKey": "%(firstKey)s"})
   print(myDictionary['secondKey'])  # outputs 'valueToRef'
"""

class dic(dict):
    def __getitem__(self, item):
        if type(dict.__getitem__(self, item)) == int: #  integer eval rtn int
            return int(str(dict.__getitem__(self, item)) % self)

        elif isinstance(dict.__getitem__(self, item), bool): #  boolean eval rtn bool
            return bool(str(dict.__getitem__(self, item)) % self)

        else: #  string & arg eval
            x = dict.__getitem__(self,item)

            if type(x) == str and x.startswith("$"):
                #  following block checks if the qouted params are int or str
                y = x[1:] % self
                strtIndex = y.find("\'" )
                endIndex = y.find("\'", strtIndex+1)

                digit = y[strtIndex+1: endIndex]

                if digit.isdigit(): return eval(y.replace("'", '')) #  return statement with integer params
                else: return eval(x[1:].strip("'")) #  return statement with string params

            if isinstance(x, list) or isinstance(x, tuple):
                return x #  list support

            return dict.__getitem__(self, item) % self #  string eval rtn str
