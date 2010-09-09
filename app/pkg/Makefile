#
#  Lepton L2P Package Script
#
#  Will compress the specified folder into folder.l2p after hashing the files
#

PACKAGES=jskit.l2p ecommerce.l2p demonstrations.l2p airbinder.l2p

.phony: all clean

all: $(PACKAGES)
	ls -al $(PACKAGES);

clean:
	rm $(PACKAGES);

%.l2p: %
	./mkpkg.sh $<
