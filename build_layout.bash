#!/bin/bash
# Jacob Alexander 2015
# Arg 1: Build Directory
# Arg 2: DefaultMap
# Arg 3: Layer 1
# Arg 4: Layer 2
# etc.
# Example: ./build_layout.bash <hash> <default map> <layer1> <layer2>
#          ./build_layout.bash c3184563548ed992bfd3574a238d3289 MD1-Hacker-0.kll MD1-Hacker-1.kll
#          ./build_layout.bash c3184563548ed992bfd3574a238d3289 "" MD1-Hacker-1.kll
# NOTE: If a layer is blank, set it as ""

# Takes a layer path, moves it to the build directory then prints the layer name(s)
# "layer1 layer1a"
# Arg 1: List of file paths
layer() {
	output=""
	for file in $@; do
		filename=$(basename "${file}")
		extension="${filename##*.}"
		filename_base="${filename%.*}"
		output="${output}${filename_base} "
	done

	# Output everything except the last character unless there was nothing in this layer
	if [ "${output}" == "" ]; then
		echo ""
	else
		echo "${output::-1}"
	fi
}

BUILD_PATH="./tmp/${1}"; shift
SOURCE_PATH=$(realpath controller)

# Create build directory if necessary
mkdir -p "${BUILD_PATH}"

DEFAULT_MAP=$(layer ${1}); shift # Assign the default map

# Make sure a there are layers to assign
PARTIAL_MAPS=""
if test $# -gt 0; then
	# Assign the parital map paramters
	# Each layer is separated by a ;
	PARTIAL_MAPS=$(layer ${1}); shift
	while test $# -gt 0; do
		PARTIAL_MAPS="${PARTIAL_MAPS};$(layer ${1})"; shift
	done
fi

# Start CMake generation
cd "${BUILD_PATH}"

# Show commands
set -x

# NOTE: To add different layers -> -DPartialMaps="layer1 layer1a;layer2 layer2a;layer3"
cmake ${SOURCE_PATH} -DScanModule="MD1" -DMacroModule="PartialMap" -DOutputModule="pjrcUSB" -DDebugModule="full" -DBaseMap="defaultMap" -DDefaultMap="${DEFAULT_MAP} stdFuncMap" -DPartialMaps="${PARTIAL_MAPS}"
# Example working cmake command
#cmake ${SOURCE_PATH} -DScanModule="MD1" -DMacroModule="PartialMap" -DOutputModule="pjrcUSB" -DDebugModule="full" -DBaseMap="defaultMap" -DDefaultMap="md1Overlay stdFuncMap" -DPartialMaps="hhkbpro2"

# Build Firmware
make -j
RETVAL=$?

# Stop showing commands
set +x

# If the build failed, make clean, then build again
# Build log will be easier to read
if [ $RETVAL -ne 0 ]; then
	make clean
	make
	RETVAL=$?

	# If the build still failed, make sure to remove any old .dfu.bin files
	if [ $RETVAL -ne 0 ]; then
		rm -f *.dfu.bin
	fi
fi

exit $RETVAL

