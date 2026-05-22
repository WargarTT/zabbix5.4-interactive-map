import ClusterStaffIcon from '../../views/map/marker/cluster-staff-icon';

class StaffClusterIconCreator {
    static build(cluster) {
        let childMarkers = cluster.getAllChildMarkers();
        let counters = {
            active: 0,
            inactive: 0,
            unknown: 0,
        };

        childMarkers.forEach(staffMarker => {
            if (staffMarker.isUnknown()) {
                counters.unknown++;
            } else if (staffMarker.isInactive()) {
                counters.inactive++;
            } else {
                counters.active++;
            }
        });

        return new ClusterStaffIcon({
            counters: counters,
        });
    }
}

export default StaffClusterIconCreator;
